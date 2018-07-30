<?php
/**
 * propagate plugin for Craft CMS 3.x
 *
 * Propagate entries between sites
 *
 * @link      inwave.eu
 * @copyright Copyright (c) 2018 Mariusz Stróż
 */

namespace mrstroz\propagate\models;

use mrstroz\propagate\Propagate;

use Craft;
use craft\base\Model;

/**
 * PropagateModel Model
 *
 * Models are containers for data. Just about every time information is passed
 * between services, controllers, and templates in Craft, it’s passed via a model.
 *
 * https://craftcms.com/docs/plugins/models
 *
 * @author    Mariusz Stróż
 * @package   Propagate
 * @since     0.0.1
 */
class PropagateModel extends Model
{
    // Public Properties
    // =========================================================================

    /**
     * Some model attribute
     *
     * @var string
     */
    public $source_site_id;
    public $destination_site_id;
    public $section_id;

    // Public Methods
    // =========================================================================

    /**
     * Returns the validation rules for attributes.
     *
     * Validation rules are used by [[validate()]] to check if attribute values are valid.
     * Child classes may override this method to declare different validation rules.
     *
     * More info: http://www.yiiframework.com/doc-2.0/guide-input-validation.html
     *
     * @return array
     */
    public function rules()
    {
        return [
            ['source_site_id', 'required'],
            ['destination_site_id', 'required'],
            ['section_id', 'required'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'source_site_id' => Craft::t('propagate', 'Source site'),
            'destination_site_id' => Craft::t('propagate', 'Destination site'),
            'section_id' => Craft::t('propagate', 'Section'),
        ];
    }
}
