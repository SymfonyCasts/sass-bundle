<?php

namespace Symfonycasts\SassBundle;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Filesystem\Path;

class SassFileHelper
{
    /**
     * Expands a configured sass path into concrete input files.
     *
     * Supports:
     *  - an existing file
     *  - an existing directory (equivalent to <dir>/ ** / *)
     *  - glob patterns including ** (matched against relative path under the base dir)
     *
     * Ignores files whose *basename* starts with "_" (Sass partials).
     *
     * @return array<string> absolute file paths
     */
    public function resolveSassInputs(string $sassPath, ?string $baseDir = null): array
    {
        if (null !== $baseDir && !Path::isAbsolute($sassPath)) {
            $sassPath = Path::makeAbsolute($sassPath, $baseDir);
        }

        // 1) Directory: treat as "<dir>/**/*" (any extension)
        if (is_dir($sassPath)) {
            $finder = new Finder();
            $finder
                ->files()
                ->in($sassPath) // recursive by default
                ->ignoreDotFiles(true)
                ->ignoreVCS(true)
                ->notName('_*')
                ->sortByName()
            ;

            $files = [];
            foreach ($finder as $file) {
                $files[] = $file->getRealPath() ?: $file->getPathname();
            }

            return $files;
        }

        // 2) Exact file
        if (is_file($sassPath)) {
            return str_starts_with(basename($sassPath), '_') ? [] : [$sassPath];
        }

        // 3) Glob/pattern (supports **)
        if (!$this->looksLikeGlob($sassPath)) {
            // Not a glob, so just return the path as-is
            return [$sassPath];
        }

        [$baseDirFromGlob, $relativeGlob] = $this->splitGlobBaseDir($sassPath);

        if (!is_dir($baseDirFromGlob)) {
            throw new \Exception(\sprintf('Could not find Sass directory: "%s" (from "%s")', $baseDirFromGlob, $sassPath));
        }

        $regex = $this->globToRegex($relativeGlob);

        $finder = new Finder();
        $finder
            ->files()
            ->in($baseDirFromGlob) // recursive
            ->ignoreDotFiles(true)
            ->ignoreVCS(true)
            ->notName('_*')
            ->sortByName()
        ;

        $files = [];
        foreach ($finder as $file) {
            $rel = str_replace('\\', '/', $file->getRelativePathname());

            if (!preg_match($regex, $rel)) {
                continue;
            }

            $files[] = $file->getRealPath() ?: $file->getPathname();
        }

        return $files;
    }

    public static function hashFilename(string $filename): string
    {
        // Normalize the path to avoid issues with different OS.
        $normalized = realpath($filename) ?: $filename;
        $normalized = str_replace('\\', '/', $normalized);

        if (str_starts_with(strtolower(PHP_OS), 'win')) {
            $normalized = strtolower($normalized);
        }

        // Hash the file path to create a unique filename.
        $hash = substr(sha1($normalized), 0, 10);

        return $filename.'-'.$hash;
    }

    private function looksLikeGlob(string $path): bool
    {
        return strpbrk($path, '*?[') !== false;
    }

    /**
     * Splits an absolute glob path into:
     *  - base directory (no glob tokens)
     *  - remaining glob relative to that base directory
     *
     * @return array{0:string,1:string}
     */
    private function splitGlobBaseDir(string $absoluteGlobPath): array
    {
        $p = str_replace('\\', '/', $absoluteGlobPath);

        $positions = array_filter([
            strpos($p, '*'),
            strpos($p, '?'),
            strpos($p, '['),
        ], static fn ($v) => $v !== false);

        $firstGlobPos = min($positions);

        $slashPos = strrpos(substr($p, 0, $firstGlobPos), '/');
        if ($slashPos === false) {
            return ['.', $p];
        }

        $baseDir = substr($p, 0, $slashPos);
        $relativeGlob = ltrim(substr($p, $slashPos + 1), '/');

        return [$baseDir, $relativeGlob];
    }

    /**
     * Converts a glob (using "/" separators) into a regex that matches the *entire* relative pathname.
     *
     * Tokens:
     *  - ** => ".*" (any number of path segments)
     *  - *  => "[^/]*"
     *  - ?  => "[^/]"
     *  - [..] character class is passed through best-effort
     */
    private function globToRegex(string $glob): string
    {
        $g = str_replace('\\', '/', $glob);

        $re = '';
        $len = strlen($g);

        for ($i = 0; $i < $len; $i++) {
            $ch = $g[$i];

            if ($ch === '*' && ($i + 1 < $len) && $g[$i + 1] === '*') {
                $i++;
                $re .= '.*';
                continue;
            }

            if ($ch === '*') {
                $re .= '[^/]*';
                continue;
            }

            if ($ch === '?') {
                $re .= '[^/]';
                continue;
            }

            if ($ch === '[') {
                $end = strpos($g, ']', $i + 1);
                if ($end === false) {
                    $re .= '\[';
                } else {
                    $re .= substr($g, $i, $end - $i + 1);
                    $i = $end;
                }
                continue;
            }

            if ($ch === '~') {
                $re .= '\~';
                continue;
            }

            if (preg_match('/[.()+^$|{}\\\\]/', $ch)) {
                $re .= '\\'.$ch;
            } else {
                $re .= $ch;
            }
        }

        return '~^'.$re.'$~u';
    }
}