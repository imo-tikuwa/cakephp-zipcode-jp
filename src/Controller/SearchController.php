<?php
namespace ZipcodeJp\Controller;

use Cake\Event\Event;
use ZipcodeJp\Controller\AppController;
use ZipcodeJp\Util\ZipcodeJpUtils;

/**
 * Index Controller
 *
 * @property \ZipcodeJp\Model\Table\ZipcodeJpsTable $ZipcodeJps
 */
class SearchController extends AppController
{
    /**
     * {@inheritDoc}
     * before filter
     */
    public function beforeFilter(\Cake\Event\EventInterface $event)
    {
        parent::beforeFilter($event);
        $this->loadComponent('RequestHandler');
        $this->viewBuilder()->setClassName('Json');

        $this->loadModel('ZipcodeJp.ZipcodeJps');
    }

    /**
     * Index method
     *
     * @return \Cake\Http\Response|void
     */
    public function index()
    {
        $zipcode = $this->getRequest()->getParam('zipcode');
        $results = $this->ZipcodeJps->findByZipcode($zipcode);
        $this->set([
            'results' => $results,
            '_serialize' => 'results',
            '_jsonOptions' => JSON_UNESCAPED_UNICODE
        ]);
    }
}
