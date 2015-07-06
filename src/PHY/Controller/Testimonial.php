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

    use PHY\Model\Testimonial as Model;
    use PHY\Variable\Str;

    /**
     * Testimonial page.
     *
     * @package PHY\Controller\Testimonial
     * @category PHY\Briggs
     * @copyright Copyright (c) 2014 Briggs Academy of Mixed Martial Arts (http://www.briggsmma.com/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy <john@jo.mu>
     */
    class Testimonial extends AController
    {

        /**
         *
         * GET /testimonial/view/slug
         * GET /testimonial
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
                $item = $manager->load(['id' => $slug], $model);
                if (!$item->exists()) {
                    return $this->redirect('/testimonial');
                }
                $head->setVariable('title', $item->title);
                $head->setVariable('description', (new Str($item->title . ': ' . $item->content))->toShorten(160));

                $content->setTemplate('testimonial/view.phtml');
                $content->setVariable('item', $item);
            } else {
                $head->setVariable('title', 'Testimonials');
                $head->setVariable('description', 'Read some reviews and testimonials about Briggs MMA.');

                /* @var \PHY\Model\Testimonial\Collection $collection */
                $collection = $manager->getCollection('Testimonial');
                $content->setVariable('collection', $collection);
            }
        }

    }
