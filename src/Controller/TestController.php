<?php
namespace ZipcodeJp\Controller;

use ZipcodeJp\Controller\AppController;

/**
 * Test Controller
 */
class TestController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null
     */
    public function index()
    {
        $this->viewBuilder()->disableAutoLayout();
    }
}
