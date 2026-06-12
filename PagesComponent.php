<?php

namespace Apps\Tms\Components\Pages;

use League\Flysystem\FilesystemException;
use League\Flysystem\UnableToCheckExistence;
use League\Flysystem\UnableToDeleteFile;
use League\Flysystem\UnableToReadFile;
use League\Flysystem\UnableToWriteFile;
use System\Base\BaseComponent;

class PagesComponent extends BaseComponent
{
    protected $pages;

    public function initialize()
    {
        $this->pages = $this->basepackages->pages;
    }

    /**
     * @acl(name=view)
     */
    public function viewAction()
    {
        if (isset($this->getData()['id'])) {
            $this->view->mode = 'edit';

            $this->view->contentSources =
                [
                    'file' => [
                        'source'=> 'file',
                        'name'  => 'HTML File'
                    ],
                    'code' => [
                        'source'=> 'code',
                        'name'  => 'HTML Code'
                    ],
                ];

            $this->view->apps = $this->apps->getAppsByType($this->app['app_type']);

            if ($this->getData()['id'] != 0) {
                $page = $this->pages->getById($this->getData()['id']);

                if (!$page) {
                    return $this->throwIdNotFound();
                }

                if (!isset($this->getData()['edit'])) {
                    if (!in_array($this->apps->getAppInfo()['route'], $page['visible_on_apps'])) {
                        return $this->throwIdNotFound();
                    }
                    if ($this->apps->getAppInfo()['app_type'] !== $page['app_type']) {
                        return $this->throwIdNotFound();
                    }

                    $this->getQueryArr['id'] = null;//Add this to disable token generation on page view.

                    $this->view->mode = 'view';

                    unset($this->view->contentSources);
                    unset($this->view->apps);

                    if ($page['content_source'] === 'file') {
                        //Check file existence
                        try {
                            $path = str_replace(base_path(), '', $this->view->getViewsDir());

                            if ($this->localContent->fileExists($path . 'pages/files/' . $page['html_file'] . '.html')) {
                                $page['html_code'] = $this->view->getPartial('pages/files/' . $page['html_file']);
                            } else {
                                $this->setErrorDispatcher('templateError');

                                return;
                            }
                        } catch (\throwable | FilesystemException | UnableToCheckExistence | UnableToReadFile $e) {
                            throw $e;
                        }
                    } else {
                        if (strpos($page['html_code'], '<script>') || strpos($page['html_code'], '</script>')) {
                            $page['html_code'] = 'JavaScript is not supported in pages. Page will not be processed!';

                            $this->view->setViewsDir($this->modules->views->getPhalconViewPath());

                            $this->view->page = $page;

                            return;
                        }

                        //We write the content of the file to a temp location and then read the content again in case we need to render VOLT
                        if (str_contains($page['html_code'], '{%') || str_contains($page['html_code'], '{{')) {
                            try {
                                $path = str_replace(base_path(), '', $this->view->getViewsDir());

                                if ($this->localContent->fileExists($path . 'pages/files/' . $page['name'] . '_temp.html')) {
                                    $this->localContent->delete($path . 'pages/files/' . $page['name'] . '_temp.html');
                                }

                                $this->localContent->write($path . 'pages/files/' . $page['name'] . '_temp.html', $page['html_code']);

                                $page['html_code'] = $this->view->getPartial('pages/files/' . $page['name'] . '_temp');
                            } catch (\throwable | FilesystemException | UnableToCheckExistence | UnableToReadFile | UnableToWriteFile | UnableToDeleteFile $e) {
                                throw $e;
                            }
                        }
                    }

                    $page = $this->pages->processWidgets($page);

                    $this->view->setViewsDir($this->modules->views->getPhalconViewPath());
                }

                $this->view->page = $page;
            }

            $this->view->pick('pages/view');

            return;
        } else {
            if ($this->dispatcher->wasForwarded()) {
                $pageId = 0;

                if ($this->access->auth->account()) {
                    if (isset($this->app['settings']['defaultUserPage'])) {
                        $pageId = (int) $this->app['settings']['defaultUserPage'];
                    }
                } else {
                    if (isset($this->app['settings']['defaultGuestPage'])) {
                        $pageId = (int) $this->app['settings']['defaultGuestPage'];
                    }
                }

                $this->getQueryArr['id'] = $pageId;

                return $this->viewAction();
            }
        }

        return $this->setErrorDispatcher('controllerNotFound');
    }
}