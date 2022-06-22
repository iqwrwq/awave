<?php

namespace awave\directory;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class DirectoryReader
{
    public function getAllProjectsFromRootDirectory(): array
    {
        $folders = array_filter(glob('*'), 'is_dir');
        $projects = array();
        foreach ($folders as $folder) {
            array_push($projects, $this->createAndAnalyseProjectFrom($folder));
        }
        return $projects;
    }

    public function projectNameExistsInRootDirectory(string $projectName): bool
    {
        return in_array($projectName, array_filter(glob('*'), 'is_dir'));
    }

    public function getProjectContent(string $projectName, &$results = array())
    {
        $files = scandir($projectName);

        foreach ($files as $key => $value) {
            $path = $projectName . DIRECTORY_SEPARATOR . $value;
            if (!is_dir($path)) {
                $results[] = basename($path);
            } else if ($value != "." && $value != ".." && $value != ".idea" && $value != ".git") {
                $this->getProjectContent($path, $results[$path]);
            }
        }
        return $results;
    }

    private function createAndAnalyseProjectFrom(string $pathToProject): array
    {
        return array(
            "name" => $pathToProject
//            "entry" => $this->resolveEntry($pathToProject)
        );
    }

    private function resolveEntry(string $pathToProject)
    {
        $target = array('index.php', 'index.html');
        foreach (scandir($pathToProject) as $item) {
            if (in_array($item, $target)) {
                return $pathToProject;
            }
        }
        $it = new RecursiveDirectoryIterator("$pathToProject");
        foreach (new RecursiveIteratorIterator($it) as $file) {
            $haystack = explode('/', $file);
            if (count(array_intersect($haystack, $target)) > 0) {
                return $file;
            }
        }
        return null;
    }

    public function getDirData($path): array
    {
        $iterator = $this->getRecursiveDirectoryIterator($path);
        $isGitRepo = false;
        $bytes = 0;
        $unit = '';
        $files[] = "";
        $file_counter = 0;

        list($files, $bytes, $file_counter, $isGitRepo) = $this->analyzeDirectoryData($iterator, $files, $bytes, $file_counter, $isGitRepo);
        list($bytes, $unit) = $this->transformFileBits($bytes, $unit);

        return array(
            'total_files' => $file_counter,
            'total_size' => $bytes,
            'unit' => $unit,
            'files' => $files,
            'isGitRepo' => $isGitRepo
        );
    }

    private function getRecursiveDirectoryIterator($path): RecursiveDirectoryIterator
    {
        $recursiveDirectoryIterator = new RecursiveDirectoryIterator($path);
        $recursiveDirectoryIterator->setFlags(FilesystemIterator::SKIP_DOTS);

        return $recursiveDirectoryIterator;
    }

    private function transformFileBits($bytes, $unit): array
    {
        if ($bytes / 1100000000 >= 1) {
            $bytes = number_format(($bytes / 1100000000));
            $unit = 'gb';
        } elseif ($bytes / 1000 >= 1) {
            $bytes = number_format(($bytes / 1000));
            $unit = 'mb';
        } else {
            $bytes = number_format($bytes);
            $unit = 'bytes';
        }
        return array($bytes, $unit);
    }

    private function analyzeDirectoryData(RecursiveDirectoryIterator $iterator, array $files, $bytes, int $file_counter, bool &$isGitRepo): array
    {
        foreach (new RecursiveIteratorIterator($iterator) as $filename => $cur) {
            $sections = explode(DIRECTORY_SEPARATOR, $cur);

            if (in_array('vendor', $sections) || in_array('node_modules', $sections)) {
                continue;
            } elseif (!in_array('.git', $sections)) {
                $files[] = $filename;
                $filesize = $cur->getSize();
                $bytes += $filesize;
                $file_counter++;
            } else {
                $isGitRepo = true;
            }

        }
        return array($files, $bytes, $file_counter, $isGitRepo);
    }

}