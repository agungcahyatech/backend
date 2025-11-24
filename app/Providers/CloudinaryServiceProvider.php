<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Cloudinary\Cloudinary as CloudinarySDK;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\Config;

class CloudinaryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton('cloudinary', function ($app) {
            return new CloudinarySDK([
                'cloud' => [
                    'cloud_name' => config('filesystems.disks.cloudinary.cloud_name'),
                    'api_key' => config('filesystems.disks.cloudinary.api_key'),
                    'api_secret' => config('filesystems.disks.cloudinary.api_secret'),
                ],
            ]);
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Storage::extend('cloudinary', function ($app, $config) {
            $adapter = new class($app['cloudinary'], $config) implements FilesystemAdapter {
                private $cloudinary;
                private $config;

                public function __construct($cloudinary, $config)
                {
                    $this->cloudinary = $cloudinary;
                    $this->config = $config;
                }

                public function fileExists(string $path): bool
                {
                    try {
                        $this->cloudinary->adminApi()->asset($path);
                        return true;
                    } catch (\Exception $e) {
                        return false;
                    }
                }

                public function write(string $path, string $contents, Config $config = null): void
                {
                    $this->cloudinary->uploadApi()->upload($contents, [
                        'public_id' => $path,
                        'resource_type' => 'auto'
                    ]);
                }

                public function writeStream(string $path, $contents, Config $config = null): void
                {
                    $this->write($path, stream_get_contents($contents), $config);
                }

                public function read(string $path): string
                {
                    $result = $this->cloudinary->adminApi()->asset($path);
                    return file_get_contents($result['secure_url']);
                }

                public function readStream(string $path)
                {
                    $result = $this->cloudinary->adminApi()->asset($path);
                    return fopen($result['secure_url'], 'r');
                }

                public function delete(string $path): void
                {
                    $this->cloudinary->uploadApi()->destroy($path);
                }

                public function directoryExists(string $path): bool
                {
                    return false; // Cloudinary doesn't have directories
                }

                public function createDirectory(string $path, Config $config = null): void
                {
                    // Cloudinary doesn't support directories
                }

                public function deleteDirectory(string $path): void
                {
                    // Cloudinary doesn't support directories
                }

                public function setVisibility(string $path, string $visibility): void
                {
                    // Cloudinary files are always public
                }

                public function visibility(string $path): string
                {
                    return 'public';
                }

                public function mimeType(string $path): string
                {
                    $result = $this->cloudinary->adminApi()->asset($path);
                    return $result['format'] ?? 'application/octet-stream';
                }

                public function lastModified(string $path): int
                {
                    $result = $this->cloudinary->adminApi()->asset($path);
                    return strtotime($result['created_at']);
                }

                public function fileSize(string $path): int
                {
                    $result = $this->cloudinary->adminApi()->asset($path);
                    return $result['bytes'] ?? 0;
                }

                public function listContents(string $path, bool $deep = false): iterable
                {
                    // This is a simplified implementation
                    return [];
                }

                public function move(string $source, string $destination, Config $config = null): void
                {
                    $this->copy($source, $destination, $config);
                    $this->delete($source);
                }

                public function copy(string $source, string $destination, Config $config = null): void
                {
                    $sourceResult = $this->cloudinary->adminApi()->asset($source);
                    $this->cloudinary->uploadApi()->upload($sourceResult['secure_url'], [
                        'public_id' => $destination,
                        'resource_type' => 'auto'
                    ]);
                }
            };

            return new Filesystem($adapter, $config);
        });
    }
} 