<?php
namespace ZipcodeJp\Controller;

use Cake\View\JsonView;
use ZipcodeJp\Controller\AppController;

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
        $this->viewBuilder()->setClassName(JsonView::class);

        $this->ZipcodeJps = $this->fetchTable('ZipcodeJp.ZipcodeJps');
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
        $this->set('results', $results);
        $this->viewBuilder()->setOptions([
            'serialize' => ['results'],
            'jsonOptions' => JSON_UNESCAPED_UNICODE
        ]);
    }
}
