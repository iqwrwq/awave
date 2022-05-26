<?php

namespace awave\pageloader;

use awave\config\Config;
use awave\directory\DirectoryReader;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;


class PageController
{
    private FilesystemLoader $loader;
    private Environment $twig;
    private DirectoryReader $directoryReader;
    private Config $config;

    public function __construct()
    {
        $this->loader = new FilesystemLoader('.aw/src/pages', getcwd());
        $this->twig = new Environment($this->loader);
        $this->directoryReader = new DirectoryReader(__DIR__);
        $this->config = new Config();
    }

    public function index()
    {
        if (isset($_GET['project'])) {
            if ($this->directoryReader->projectNameExistsInRootDirectory($_GET['project'])){
                echo $this->twig->render('project.html.twig',
                    [
                        "name" => $_GET['project'],
                        "content" => $this->directoryReader->getProjectContent($_GET['project']),
                        "dir_data" => $this->directoryReader->getDirData($_GET['project']),
                    ]);
            }else{
                echo $this->twig->render('404.html.twig');
            }
        }elseif (isset($_GET['configure'])){
            echo $this->twig->render('index.html.twig',
                [
                    "projects" => $this->directoryReader->getAllProjectsFromRootDirectory(),
                    "theme" => $this->config->getTheme(),
                ]);
        } else{
            echo $this->twig->render('index.html.twig',
                [
                    "projects" => $this->directoryReader->getAllProjectsFromRootDirectory(),
                    "theme" => $this->config->getTheme(),
                ]);
        }
    }
}