<?php

namespace awave\directory;

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
            "name" => $pathToProject,
            "labels" => $this->resolveLabels($pathToProject),
            "entry" => $this->resolveEntry($pathToProject)
        );
    }

    private function resolveLabels(string $pathToProject)
    {
        $labels = array();

        if (is_dir($pathToProject . '/.peck')) {
            $labels .= '.peck';
        }
        if (is_dir($pathToProject . '/.git')) {
            array_push($labels, 'git-square');
        }
        return $labels;
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
        $ite = new RecursiveDirectoryIterator($path);
        $ite->setFlags(RecursiveDirectoryIterator::SKIP_DOTS);

        $bytestotal = 0;
        $nbfiles = 0;
        $files[] = "";
        foreach (new RecursiveIteratorIterator($ite) as $filename => $cur) {
            $sections = explode(DIRECTORY_SEPARATOR, $cur);
            if (!in_array('.idea', $sections) && !in_array('.git', $sections)) {
                $filesize = $cur->getSize();
                $bytestotal += $filesize;
                $files[] = $filename;
                $nbfiles++;
            }
        }
        $bytestotal = number_format($bytestotal);
        return array('total_files' => $nbfiles, 'total_size' => $bytestotal,'files' => $files);
    }

//    public function getDirCount($path): int
//    {
//        echo "<pre>";
//        print_r(glob($path . "/*", GLOB_ONLYDIR));
//        echo "</pre>";
//        return count(glob($path . "/*", GLOB_ONLYDIR));
//    }


}
