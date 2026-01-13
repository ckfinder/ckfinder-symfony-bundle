<?php
/*
 * This file is a part of the CKFinder bundle for Symfony.
 *
 * Copyright (c) 2022, CKSource Holding sp. z o.o. All rights reserved.
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
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'button_text' => 'Browse',
            'button_attr' => [],
            'mode'        => 'popup'
        ]);

        $allowedTypes = [
            'button_text' => 'string',
            'button_attr' => 'array',
            'mode'        => 'string'
        ];

        foreach ($allowedTypes as $option => $allowedType) {
            $resolver->addAllowedTypes($option, $allowedType);
        }

        $resolver->setAllowedValues('mode', ['popup', 'modal']);
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['button_text'] = $options['button_text'];
        $view->vars['button_attr'] = $options['button_attr'];
        $view->vars['mode'] = $options['mode'];
        $view->vars['button_id'] = 'ckf_filechooser_' . $view->vars['id'];
    }

    /**
     * {@inheritdoc}
     */
    public function getParent(): string
    {
        return TextType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return 'ckfinder_file_chooser';
    }
}
