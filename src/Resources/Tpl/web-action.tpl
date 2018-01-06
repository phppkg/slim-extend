
    /**
     * a web controller action
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function {@action}{@suffix}()
    {
        // do something ...
        return $this->renderString('hello, this is: ' . __METHOD__);
    }
