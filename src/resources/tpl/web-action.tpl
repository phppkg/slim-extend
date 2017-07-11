    /**
     * a web controller action
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function {@action}Action()
    {
        // do something ...
        $this->renderString('hello, this is: ' . __METHOD__);
    }
