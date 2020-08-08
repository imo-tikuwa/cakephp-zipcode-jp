<?php
namespace ZipcodeJp\Model\Entity;

use Cake\ORM\Entity;

/**
 * ZipcodeJp Entity
 *
 * @property int $id
 * @property string $zipcode
 * @property string $pref
 * @property string $city
 * @property string $address
 */
class ZipcodeJp extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array
     */
    protected $_accessible = [
        'zipcode' => true,
        'pref' => true,
        'city' => true,
        'address' => true,
    ];
}
