<?php
namespace ZipcodeJp\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * ZipcodeJps Model
 *
 * @method \ZipcodeJp\Model\Entity\ZipcodeJp get($primaryKey, $options = [])
 * @method \ZipcodeJp\Model\Entity\ZipcodeJp newEntity($data = null, array $options = [])
 * @method \ZipcodeJp\Model\Entity\ZipcodeJp[] newEntities(array $data, array $options = [])
 * @method \ZipcodeJp\Model\Entity\ZipcodeJp|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \ZipcodeJp\Model\Entity\ZipcodeJp saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \ZipcodeJp\Model\Entity\ZipcodeJp patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \ZipcodeJp\Model\Entity\ZipcodeJp[] patchEntities($entities, array $data, array $options = [])
 * @method \ZipcodeJp\Model\Entity\ZipcodeJp findOrCreate($search, callable $callback = null, $options = [])
 */
class ZipcodeJpsTable extends Table
{
    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->setTable('zipcode_jps');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator)
    {
        $validator
            ->integer('id')
            ->allowEmptyString('id', null, 'create');

        $validator
            ->scalar('zipcode')
            ->maxLength('zipcode', 7)
            ->requirePresence('zipcode', 'create')
            ->notEmptyString('zipcode');

        $validator
            ->scalar('pref')
            ->maxLength('pref', 255)
            ->requirePresence('pref', 'create')
            ->notEmptyString('pref');

        $validator
            ->scalar('city')
            ->maxLength('city', 255)
            ->requirePresence('city', 'create')
            ->notEmptyString('city');

        $validator
            ->scalar('address')
            ->maxLength('address', 255)
            ->requirePresence('address', 'create')
            ->notEmptyString('address');

        return $validator;
    }

    /**
     * 郵便番号を元に住所情報を取得する
     * @param string $zipcode 郵便番号
     * @return array|null
     */
    public function findByZipcode($zipcode = null)
    {
        if (is_null($zipcode)) {
            return null;
        }

        return $this->find()
        ->select([
            'pref',
            'city',
            'address',
        ])
        ->where([
            'zipcode' => $zipcode,
        ])
        ->enableHydration(false)
        ->toArray();
    }
}
