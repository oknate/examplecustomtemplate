<?php

namespace Drupal\formcustomtemplate\Form;

use Drupal\Core\Url;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Views;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\taxonomy\Entity\Term;

/**
 * Class LifeCenterSearchForm.
 *
 * @package Drupal\abilitylab_contextual_content\Form
 */
class ExampleForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'my_form_test';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['textfieldtest'] = [
      '#type' => 'textfield',
      '#title' => $this->t('textfieldtest'),
      '#maxlength' => 64,
      '#size' => 64,
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => t('Submit'),
    ];

    return $form;
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {
    dump('test validateForm'); die();
    // logs never displayed when I use my custom template. If I don't use the custom template, this log appears.
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    dump('test submitForm'); die();
    // logs never displayed when I use my custom template. If I don't use the custom template, this log appears.
  }

}
