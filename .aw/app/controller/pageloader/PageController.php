<?php

namespace awave\pageloader;

use awave\config\Config;
use awave\directory\DirectoryReader;
use Twig\Environment;
use Twig\Extension\DebugExtension;
use Twig\Loader\FilesystemLoader;


class PageController
{
    private FilesystemLoader $loader;
    private Environment $twig;
    private DirectoryReader $directoryReader;
    private Config $config;

    public function __construct()
    {
        $this->loader = new FilesystemLoader('.aw/app/pages', getcwd());
        $this->twig = new Environment($this->loader, [
            'debug' => true
        ]);
        $this->twig->addExtension(new DebugExtension());
        $this->directoryReader = new DirectoryReader(__DIR__);
        $this->config = new Config();
    }

    public function index()
    {
        if (isset($_GET['project'])) {
            if ($this->directoryReader->projectNameExistsInRootDirectory($_GET['project'])) {
                echo $this->twig->render('index.html.twig',
                    [
                        "isDashboard" => false,
                        "navigation_root_snippet" => $this->getNavigationRootSnippet(),
                        "name" => $_GET['project'],
                        "content" => $this->directoryReader->getProjectContent($_GET['project']),
                        "dir_data" => $this->directoryReader->getDirData($_GET['project']),
                        "version" => "v0.1 AKAKO"
                    ]);
            } else {
                echo $this->twig->render('404.html.twig');
            }
        } else {
            echo $this->twig->render('index.html.twig',
                [
                    "isDashboard" => true,
                    "navigation_root_snippet" => $this->getNavigationRootSnippet(),
                    "projects" => $this->directoryReader->getAllProjectsFromRootDirectory(),
                    "version" => "v0.1 AKAKO"
                ]);
        }
    }

    private function getNavigationRootSnippet()
    {
        $fullRootDirectoryPath = $_SERVER['DOCUMENT_ROOT'];
        $rootDirectorySnippets = explode('/', $fullRootDirectoryPath);

        if (count($rootDirectorySnippets) > 2) {
            $sliced = array_slice($rootDirectorySnippets, -2, 2, true);
            array_unshift($sliced, '...');
            return $sliced;
        }
        return $rootDirectorySnippets;
    }
}
