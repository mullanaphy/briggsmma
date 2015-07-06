<?php

    /**
     * Phyneapple!
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

    use PHY\Model\Config as ConfigModel;
    use Michelf\Markdown;

    /**
     * Home page.
     *
     * @package PHY\Controller\Index
     * @category PHY\Phyneapple
     * @copyright Copyright (c) 2013 Phyneapple! (http://www.phyneapple.com/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy <john@jo.mu>
     */
    class Index extends \PHY\Controller\AController
    {

        /**
         * GET /
         */
        public function index_get()
        {
            $layout = $this->getLayout();

            $head = $layout->block('head');
            $head->setVariable('title', 'Welcome to Briggs MMA.');
            $head->setVariable('description', 'A fun lively environment to learn grappling and striking from high level instructors.');

            $body = $layout->block('layout');
            $app = $this->getApp();

            /* @var \PHY\Model\User $user */
            $user = $app->getUser();

            /* @var \PHY\Database\IDatabase $database */
            $database = $app->get('database');
            $manager = $database->getManager();

            $cache = $app->get('cache');
            if (!$welcome = $cache->get('index/welcome/rendered')) {
                $welcome = $manager->load(['key' => 'welcome'], new ConfigModel);
                $welcome = Markdown::defaultTransform($welcome->value);
                $cache->set('index/welcome/rendered', $welcome, 86400 * 31);
            }

            $jumbotron = $manager->load(['key' => 'jumbotron'], new ConfigModel);
            $body->setChild('jumbotron', [
                'template' => 'index/jumbotron.phtml',
                'content' => $jumbotron->value,
                'welcome' => $welcome
            ]);

            /* @var \PHY\Model\Blog\Collection $collection */
            $collection = $manager->getCollection('Blog');
            $collection->where()->field('visible')->in($user->getVisibility())->field('deleted')->is(0);
            $collection->order()->by('created')->direction('desc');
            $collection->limit(3);

            $content = $layout->block('content');
            $content->setVariable('collection', $collection);

            $content->setChild('index/schedule', [
                'template' => 'index/schedule.phtml',
                'viewClass' => 'index/schedule'
            ]);
            $content->setChild('index/testimonial', [
                'template' => 'index/testimonial.phtml',
                'viewClass' => 'index/testimonial'
            ]);
        }

    }
