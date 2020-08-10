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
    public function beforeFilter(Event $event)
    {
        parent::beforeFilter($event);
        $this->loadComponent('RequestHandler');
        $this->viewBuilder()->setClassName('Json');

        $this->loadModel('ZipcodeJps');
    }

    /**
     * Index method
     *
     * @return \Cake\Http\Response|null
     */
    public function index()
    {
        $zipcode = $this->request->getParam('zipcode');
        $results = $this->ZipcodeJps->findByZipcode($zipcode);
        $this->set([
            'results' => $results,
            '_serialize' => 'results',
            '_jsonOptions' => JSON_UNESCAPED_UNICODE
        ]);
    }
}
