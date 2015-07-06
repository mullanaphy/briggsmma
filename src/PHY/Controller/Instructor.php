<?php

    /**
     * Briggs Academy of Mixed Martial Arts
     *
     * LICENSE
     *
     * This source file is subject to the Open Software License (OSL 3.0)
     * that is bundled with this package in the file LICENSE.txt.
     * It is also available through the world-wide-web at this URL:
     * http://opensource.org/licenses/osl-3.0.php
     * If you did not receive a copy of the license and are unable to
     * obtain it through the world-wide-web, please send an email
     * to license@phyneapple.com so we can send you a copy immediately.
     *
     */

    namespace PHY\Controller;

    use PHY\Model\User as Model;
    use PHY\Variable\Str;

    /**
     * Instructor page.
     *
     * @package PHY\Controller\Instructor
     * @category PHY\Briggs
     * @copyright Copyright (c) 2014 Briggs Academy of Mixed Martial Arts (http://www.briggsmma.com/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy <john@jo.mu>
     */
    class Instructor extends AController
    {

        /**
         *
         * GET /instructor/view/slug
         * GET /instructor
         */
        public function index_get()
        {

            $layout = $this->getLayout();
            $head = $layout->block('head');
            $content = $layout->block('content');
            $app = $this->getApp();

            /* @var \PHY\Database\IDatabase $database */
            $database = $app->get('database');
            $manager = $database->getManager();

            $slug = $this->getRequest()->getActionName();
            if ($slug !== '__index') {
                $model = new Model;
                $item = $manager->load(['username' => $slug], $model);
                if (!$item->exists()) {
                    return $this->redirect('/instructor');
                }
                $head->setVariable('title', $item->title . ' ' . $item->name);
                $head->setVariable('description', (new Str($item->title . ' ' . $item->name . ' ' . $item->bio))->toShorten(160));

                $content->setTemplate('instructor/view.phtml');
                $content->setVariable('item', $item);
            } else {
                $head->setVariable('title', 'Instructors');
                $head->setVariable('description', 'Show case of all of our instructors here at Briggs MMA.');

                /* @var \PHY\Model\User\Collection $collection */
                $collection = $manager->getCollection('User');
                $collection->where()->field('group')->is('instructor');
                $content->setVariable('collection', $collection);
            }
        }

    }
