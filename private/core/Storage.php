<?php

class Storage
{
    /*
    |--------------------------------------------------------------------------
    | Path
    |--------------------------------------------------------------------------
    */

    protected static function path(string $path): string
    {
        return publicPath($path);
    }


    protected static function makeDirectoryPath(
        string $path,
        int $permissions = 0755
    ): bool {
        if (is_dir($path)) {
            return true;
        }

        return mkdir(
            $path,
            $permissions,
            true
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Exists
    |--------------------------------------------------------------------------
    */

    public static function exists(string $path): bool
    {
        return file_exists(
            static::path($path)
        );
    }


    public static function isFile(string $path): bool
    {
        return is_file(
            static::path($path)
        );
    }


    public static function isDirectory(string $path): bool
    {
        return is_dir(
            static::path($path)
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Copy
    |--------------------------------------------------------------------------
    */

    public static function copy(
        string $source,
        string $destination
    ): bool {
        $source = static::path($source);
        $destination = static::path($destination);

        if (!file_exists($source)) {
            return false;
        }

        if (is_file($source)) {

            static::makeDirectoryPath(
                dirname($destination)
            );

            return copy(
                $source,
                $destination
            );
        }

        return static::copyDirectory(
            $source,
            $destination
        );
    }


    protected static function copyDirectory(
        string $source,
        string $destination
    ): bool {
        if (!static::makeDirectoryPath($destination)) {
            return false;
        }

        $items = scandir($source);

        if ($items === false) {
            return false;
        }

        foreach ($items as $item) {

            if ($item === '.' || $item === '..') {
                continue;
            }

            $from = $source . DIRECTORY_SEPARATOR . $item;
            $to = $destination . DIRECTORY_SEPARATOR . $item;

            if (is_dir($from)) {

                if (!static::copyDirectory($from, $to)) {
                    return false;
                }

            } else {

                if (!copy($from, $to)) {
                    return false;
                }
            }
        }

        return true;
    }


    /*
    |--------------------------------------------------------------------------
    | Move
    |--------------------------------------------------------------------------
    */

    public static function move(
        string $source,
        string $destination
    ): bool {
        $source = static::path($source);
        $destination = static::path($destination);

        if (!file_exists($source)) {
            return false;
        }

        static::makeDirectoryPath(
            dirname($destination)
        );

        return rename(
            $source,
            $destination
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Delete
    |--------------------------------------------------------------------------
    */

    public static function delete(
        string|array $paths
    ): bool {
        $paths = is_array($paths)
            ? $paths
            : [$paths];

        $result = true;

        foreach ($paths as $path) {

            $path = static::path($path);

            if (!file_exists($path)) {
                continue;
            }

            if (is_dir($path)) {

                if (!static::deleteDirectory($path)) {
                    $result = false;
                }

                continue;
            }

            if (!unlink($path)) {
                $result = false;
            }
        }

        return $result;
    }


    protected static function deleteDirectory(
        string $directory
    ): bool {
        $items = scandir($directory);

        if ($items === false) {
            return false;
        }

        foreach ($items as $item) {

            if ($item === '.' || $item === '..') {
                continue;
            }

            $path = $directory
                . DIRECTORY_SEPARATOR
                . $item;

            if (is_dir($path)) {

                if (!static::deleteDirectory($path)) {
                    return false;
                }

            } else {

                if (!unlink($path)) {
                    return false;
                }
            }
        }

        return rmdir($directory);
    }


    /*
    |--------------------------------------------------------------------------
    | Directory
    |--------------------------------------------------------------------------
    */

    public static function makeDirectory(
        string $path,
        int $permissions = 0755
    ): bool {
        return static::makeDirectoryPath(
            static::path($path),
            $permissions
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Rename
    |--------------------------------------------------------------------------
    */

    public static function rename(
        string $source,
        string $destination
    ): bool {
        $source = static::path($source);
        $destination = static::path($destination);

        if (!file_exists($source)) {
            return false;
        }

        static::makeDirectoryPath(
            dirname($destination)
        );

        return rename(
            $source,
            $destination
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Zip
    |--------------------------------------------------------------------------
    */

    public static function zip(
        string $source,
        string $destination
    ): bool {
        $source = static::path($source);
        $destination = static::path($destination);

        if (!file_exists($source)) {
            return false;
        }

        static::makeDirectoryPath(
            dirname($destination)
        );

        $zip = new ZipArchive();

        if (
            $zip->open(
                $destination,
                ZipArchive::CREATE |
                ZipArchive::OVERWRITE
            ) !== true
        ) {
            return false;
        }

        if (is_file($source)) {

            $zip->addFile(
                $source,
                basename($source)
            );

        } else {

            $source = rtrim(
                realpath($source),
                DIRECTORY_SEPARATOR
            );

            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator(
                    $source,
                    RecursiveDirectoryIterator::SKIP_DOTS
                )
            );

            foreach ($iterator as $file) {

                if (!$file->isFile()) {
                    continue;
                }

                $filePath = $file->getRealPath();

                $relativePath = substr(
                    $filePath,
                    strlen($source) + 1
                );

                $zip->addFile(
                    $filePath,
                    $relativePath
                );
            }
        }

        return $zip->close();
    }


    /*
    |--------------------------------------------------------------------------
    | Unzip
    |--------------------------------------------------------------------------
    */

    public static function unzip(
        string $zip,
        string $destination
    ): bool {
        $zip = static::path($zip);
        $destination = static::path($destination);

        if (!is_file($zip)) {
            return false;
        }

        static::makeDirectoryPath(
            $destination
        );

        $archive = new ZipArchive();

        if ($archive->open($zip) !== true) {
            return false;
        }

        $result = $archive->extractTo(
            $destination
        );

        $archive->close();

        return $result;
    }


    /*
    |--------------------------------------------------------------------------
    | Files
    |--------------------------------------------------------------------------
    */

    public static function files(
        string $directory
    ): array {
        $directory = static::path($directory);

        if (!is_dir($directory)) {
            return [];
        }

        return array_values(
            array_filter(
                scandir($directory),
                function ($item) use ($directory) {

                    return is_file(
                        $directory
                        . DIRECTORY_SEPARATOR
                        . $item
                    );
                }
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Directories
    |--------------------------------------------------------------------------
    */

    public static function directories(
        string $directory
    ): array {
        $directory = static::path($directory);

        if (!is_dir($directory)) {
            return [];
        }

        return array_values(
            array_filter(
                scandir($directory),
                function ($item) use ($directory) {

                    return $item !== '.'
                        && $item !== '..'
                        && is_dir(
                            $directory
                            . DIRECTORY_SEPARATOR
                            . $item
                        );
                }
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | File Information
    |--------------------------------------------------------------------------
    */

    public static function size(
        string $path
    ): int {
        $path = static::path($path);

        return is_file($path)
            ? filesize($path)
            : 0;
    }


    public static function extension(
        string $path
    ): string {
        return pathinfo(
            $path,
            PATHINFO_EXTENSION
        );
    }


    public static function basename(
        string $path
    ): string {
        return basename($path);
    }


    public static function mimeType(
        string $path
    ): string|false {
        $path = static::path($path);

        return is_file($path)
            ? mime_content_type($path)
            : false;
    }


    /*
    |--------------------------------------------------------------------------
    | Content
    |--------------------------------------------------------------------------
    */

    public static function get(
        string $path
    ): string|false {
        $path = static::path($path);

        if (!is_file($path)) {
            return false;
        }

        return file_get_contents($path);
    }


    public static function put(
        string $path,
        string $content
    ): int|false {
        $path = static::path($path);

        static::makeDirectoryPath(
            dirname($path)
        );

        return file_put_contents(
            $path,
            $content
        );
    }


    public static function append(
        string $path,
        string $content
    ): int|false {
        $path = static::path($path);

        static::makeDirectoryPath(
            dirname($path)
        );

        return file_put_contents(
            $path,
            $content,
            FILE_APPEND
        );
    }
}