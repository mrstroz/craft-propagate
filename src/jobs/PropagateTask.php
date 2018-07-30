<?php
/**
 * propagate plugin for Craft CMS 3.x
 *
 * Propagate entries between sites
 *
 * @link      inwave.eu
 * @copyright Copyright (c) 2018 Mariusz Stróż
 */

namespace mrstroz\propagate\jobs;

use craft\db\QueryAbortedException;
use craft\elements\Entry;

use Craft;
use craft\queue\BaseJob;
use yii\base\Exception;

/**
 * PropagateTask job
 *
 * Jobs are run in separate process via a Queue of pending jobs. This allows
 * you to spin lengthy processing off into a separate PHP process that does not
 * block the main process.
 *
 * You can use it like this:
 *
 * use mrstroz\propagate\jobs\PropagateTask as PropagateTaskJob;
 *
 * $queue = Craft::$app->getQueue();
 * $jobId = $queue->push(new PropagateTaskJob([
 *     'description' => Craft::t('propagate', 'This overrides the default description'),
 * ]));
 *
 * The key/value pairs that you pass in to the job will set the public properties
 * for that object. Thus whatever you set 'someAttribute' to will cause the
 * public property $someAttribute to be set in the job.
 *
 * Passing in 'description' is optional, and only if you want to override the default
 * description.
 *
 * More info: https://github.com/yiisoft/yii2-queue
 *
 * @author    Mariusz Stróż
 * @package   Propagate
 * @since     0.0.1
 */
class PropagateTask extends BaseJob
{
    // Public Properties
    // =========================================================================

    /**
     * Some attribute
     *
     * @var string
     */
    public $source_site_id;
    public $destination_site_id;
    public $section_id;
    public $limit;
    public $offset;

    // Public Methods
    // =========================================================================

    /**
     * When the Queue is ready to run your job, it will call this method.
     * You don't need any steps or any other special logic handling, just do the
     * jobs that needs to be done here.
     *
     * More info: https://github.com/yiisoft/yii2-queue
     * @param $queue
     * @throws QueryAbortedException
     */
    public function execute($queue)
    {

        if (!is_numeric($this->source_site_id))
            throw new QueryAbortedException(Craft::t('propagate', '{property} not defined for {class}', ['property' => 'source_site_id', 'class' => __CLASS__]));

        if (!is_numeric($this->destination_site_id))
            throw new QueryAbortedException(Craft::t('propagate', '{property} not defined for {class}', ['property' => 'destination_site_id', 'class' => __CLASS__]));

        if (!is_numeric($this->section_id))
            throw new QueryAbortedException(Craft::t('propagate', '{property} not defined for {class}', ['property' => 'section_id', 'class' => __CLASS__]));

        if (!is_numeric($this->limit))
            throw new QueryAbortedException(Craft::t('propagate', '{property} not defined for {class}', ['property' => 'limit', 'class' => __CLASS__]));

        if (!is_numeric($this->offset))
            throw new QueryAbortedException(Craft::t('propagate', '{property} not defined for {class}', ['property' => 'offset', 'class' => __CLASS__]));


        $entries = Entry::find()->siteId($this->source_site_id)->sectionId($this->section_id)->enabledForSite(0)->status(0)->limit($this->limit)->offset($this->offset)->all();

        $currentElement = 0;
        try {
            foreach ($entries as $entry) {
                $this->setProgress($queue, $currentElement++ / $this->limit);

                \Craft::$app->elements->propagateElement($entry, $this->destination_site_id);
            }
        } catch (QueryAbortedException $e) {
            // Fail silently
        } catch (Exception $e) {
        }
    }

    // Protected Methods
    // =========================================================================

    /**
     * Returns a default description for [[getDescription()]], if [[description]] isn’t set.
     *
     * @return string The default task description
     */
    protected function defaultDescription(): string
    {
        return Craft::t('propagate', 'PropagateTask');
    }
}
