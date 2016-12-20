<?php
/*
 * This file is a part of the CKFinder bundle for Symfony.
 *
 * Copyright (C) 2016, CKSource - Frederico Knabben. All rights reserved.
 *
 * Licensed under the terms of the MIT license.
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace CKSource\Bundle\CKFinderBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * CKFinder file chooser form type.
 */
class CKFinderFileChooserType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'button_text' => 'Browse',
            'button_attr' => array(),
            'mode'        => 'popup'
        ));

        $allowedTypes = array(
            'button_text' => 'string',
            'button_attr' => 'array',
            'mode'        => 'string'
        );

        foreach ($allowedTypes as $option => $allowedType) {
            $resolver->addAllowedTypes($option, $allowedType);
        }

        $resolver->setAllowedValues('mode', array('popup', 'modal'));
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['button_text'] = $options['button_text'];
        $view->vars['button_attr'] = $options['button_attr'];
        $view->vars['mode'] = $options['mode'];
        $view->vars['button_id'] = 'ckf_filechooser_' . $view->vars['id'];
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return TextType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'ckfinder_file_chooser';
    }
}
