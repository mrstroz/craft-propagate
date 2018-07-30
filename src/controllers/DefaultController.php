<?php
/**
 * propagate plugin for Craft CMS 3.x
 *
 * Propagate entries between sites
 *
 * @link      inwave.eu
 * @copyright Copyright (c) 2018 Mariusz Stróż
 */

namespace mrstroz\propagate\controllers;

use craft\elements\Entry;
use mrstroz\propagate\jobs\PropagateTask;
use mrstroz\propagate\models\PropagateModel;

use Craft;
use craft\web\Controller;
use yii\helpers\VarDumper;

/**
 * Default Controller
 *
 * Generally speaking, controllers are the middlemen between the front end of
 * the CP/website and your plugin’s services. They contain action methods which
 * handle individual tasks.
 *
 * A common pattern used throughout Craft involves a controller action gathering
 * post data, saving it on a model, passing the model off to a service, and then
 * responding to the request appropriately depending on the service method’s response.
 *
 * Action methods begin with the prefix “action”, followed by a description of what
 * the method does (for example, actionSaveIngredient()).
 *
 * https://craftcms.com/docs/plugins/controllers
 *
 * @author    Mariusz Stróż
 * @package   Propagate
 * @since     0.0.1
 */
class DefaultController extends Controller
{

    // Protected Properties
    // =========================================================================

    /**
     * @var    bool|array Allows anonymous access to this controller's actions.
     *         The actions must be in 'kebab-case'
     * @access protected
     */
    protected $allowAnonymous = [];

    // Public Methods
    // =========================================================================

    /**
     * Handle a request going to our plugin's index action URL,
     * e.g.: actions/propagate/default
     *
     * @return mixed
     * @throws \yii\base\Exception
     */
    public function actionIndex()
    {

        $sites = [];
        $sites[] = [
            'label' => '',
            'value' => '',
        ];
        $sections = [];
        $sectionToQueue = [];

        foreach (Craft::$app->getSites()->getAllSites() as $site) {
            $sites[] = [
                'label' => $site->name,
                'value' => $site->id,
            ];
        }

        foreach (Craft::$app->getSections()->getAllSections() as $section) {
            $sections[$section->id] = [
                'label' => $section->name,
                'value' => $section->id,
            ];

            $sectionToQueue[] = $section->id;
        }

        $model = new PropagateModel();

        if (Craft::$app->request->isPost) {
            $model->load(Craft::$app->request->post(), 'propagate');
            if ($model->validate()) {

                if ($model->section_id !== '*') {
                    $sectionToQueue = $model->section_id;
                }

                foreach ($sectionToQueue as $sectionId) {
                    $count = Entry::find()->siteId($model->source_site_id)->sectionId($sectionId)->enabledForSite(0)->status(0)->count();

                    $do = true;
                    $i = 0;
                    $limit = 10;

                    if ($count > 0) {
                        while ($do) {
                            $queue = Craft::$app->getQueue()->priority(1025);

                            $jobId = $queue->push(new PropagateTask([
                                'description' => Craft::t('app', 'Propagating {section} entries from {from} to {to}', [
                                    'section' => $sections[$sectionId]['label'],
                                    'from' => $i,
                                    'to' => $i + $limit
                                ]),
                                'source_site_id' => $model->source_site_id,
                                'destination_site_id' => $model->destination_site_id,
                                'section_id' => (int)$model->section_id,
                                'limit' => $limit,
                                'offset' => $i,
                            ]));

                            $i += $limit;

                            if ($i > $count) {
                                $do = false;
                            }
                        }
                    }
                }

                Craft::$app->getSession()->setNotice(Craft::t('propagate', 'Entries added to propagation queue.'));
                return $this->refresh();
            }
        }

        return $this->renderTemplate('propagate/index', [
            'sites' => $sites,
            'sections' => $sections,
            'model' => $model
        ]);
    }

}
