<?php

namespace App\Services;

use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;

class CloudinaryException extends Exception {}

class CloudinaryService
{
    protected string $cloudName;

    protected string $apiKey;

    protected string $apiSecret;

    protected string $baseFolder;

    protected string $defaultSubfolder = 'products'; 

    public function __construct()
    {
        $this->cloudName = config('services.cloudinary.cloud_name');
        $this->apiKey = config('services.cloudinary.api_key');
        $this->apiSecret = config('services.cloudinary.api_secret');
        $this->baseFolder = config('services.cloudinary.folder', 'portfolio-project/laravel/inventory-management');
    }

    /**
     * Upload an image to Cloudinary.
     * If a specific subfolder is passed, it uses it; otherwise, it uses the default.
     */
    public function upload(UploadedFile $file, ?string $subfolder = null): array
    {
        $timestamp = time();

        // Determine which subfolder to use (passed argument takes priority, falls back to default)
        $activeSubfolder = $subfolder ?? $this->defaultSubfolder;

        // Cleanly combine base folder and subfolder (handles cases where subfolder might be empty)
        $targetFolder = $activeSubfolder
            ? trim($this->baseFolder, '/').'/'.trim($activeSubfolder, '/')
            : trim($this->baseFolder, '/');

        $paramsToSign = [
            'folder' => $targetFolder,
            'timestamp' => $timestamp,
            'use_filename' => 'false',
        ];

        $signature = $this->generateSignature($paramsToSign);
        $url = "https://api.cloudinary.com/v1_1/{$this->cloudName}/image/upload";

        try {
            $response = Http::asMultipart()
                ->connectTimeout(5)
                ->timeout(30)
                ->attach('file', file_get_contents($file->getRealPath()), $file->getClientOriginalName())
                ->post($url, array_merge($paramsToSign, [
                    'api_key' => $this->apiKey,
                    'signature' => $signature,
                ]));

            if ($response->failed()) {
                $this->handleErrorResponse($response);
            }

            $data = $response->json();

            return [
                'public_id' => $data['public_id'] ?? null,
                'secure_url' => $data['secure_url'] ?? null,
            ];
        } catch (Exception $e) {
            if ($e instanceof CloudinaryException) {
                throw $e;
            }
            throw new CloudinaryException("Cloudinary network error: {$e->getMessage()}", 500, $e);
        }
    }

    /**
     * Destroy remains universal because public_id holds the full path.
     */
    public function destroy(string $publicId): bool
    {
        $timestamp = time();

        $paramsToSign = [
            'public_id' => $publicId,
            'timestamp' => $timestamp,
        ];

        $signature = $this->generateSignature($paramsToSign);
        $url = "https://api.cloudinary.com/v1_1/{$this->cloudName}/image/destroy";

        try {
            $response = Http::asForm()
                ->connectTimeout(5)
                ->timeout(15)
                ->post($url, array_merge($paramsToSign, [
                    'api_key' => $this->apiKey,
                    'signature' => $signature,
                ]));

            if ($response->failed()) {
                $this->handleErrorResponse($response);
            }

            return ($response->json('result') ?? '') === 'ok';
        } catch (Exception $e) {
            if ($e instanceof CloudinaryException) {
                throw $e;
            }
            throw new CloudinaryException("Cloudinary network error: {$e->getMessage()}", 500, $e);
        }
    }

    protected function generateSignature(array $params): string
    {
        ksort($params);
        $queryString = collect($params)->map(fn ($v, $k) => "{$k}={$v}")->implode('&');

        return sha1($queryString.$this->apiSecret);
    }

    protected function handleErrorResponse($response): void
    {
        $status = $response->status();
        $message = $response->json('error.message') ?? $response->body();
        throw new CloudinaryException("Cloudinary API Error ({$status}): {$message}", $status);
    }
}
