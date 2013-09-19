<?php

class DefaultController extends BackController
{
    public function indexAction(HTTPRequest $request)
    {
        $message = "Hello World !";
        $this->page->addVar('message', $message);
    }
}