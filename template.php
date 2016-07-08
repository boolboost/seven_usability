<?php
/**
 * @file
 * theme seven_usability
 */

/**
 * Implements hook_form_alter().
 */
function seven_usability_form_alter(&$form, &$form_state, $form_id) {
  switch ($form_id) {
    case 'node_type_form':
      // add node type
      if (isset($form['#node_type']->is_new) && $form['#node_type']->is_new) {
        $form['submission']['node_preview']['#default_value'] = variable_get('default_values:node_type_form:node_preview', 0);
        $form['submission']['title_label']['#default_value'] = variable_get('default_values:node_type_form:title_label', 'Название');
        $form['workflow']['node_options']['#default_value'] = explode(',', variable_get('default_values:node_type_form:node_options', 'status'));
        $form['display']['node_submitted']['#default_value'] = variable_get('default_values:node_type_form:node_submitted', FALSE);
        $form['comment']['comment']['#default_value'] = variable_get('default_values:node_type_form:comment', 0);

        $form['#submit'][] = 'seven_usability__form_submit';
      }
      break;

    case 'field_ui_field_overview_form':
      // field group last type
      if (isset($form['fields']['_add_new_group']['format']['type']['#default_value'])) {
        $form['fields']['_add_new_group']['format']['type']['#default_value'] = variable_get('default_values:field_ui_field_overview_form:type', 'html-element');
      }

      $form['#submit'][] = 'seven_usability__form_submit';
      break;

    case 'field_ui_display_overview_form':
      // machine name in field display
      if (isset($form['fields'])) {
        foreach ($form['fields'] as $type => &$field) {
          if (isset($field['#row_type']) && $field['#row_type'] == 'field') {
            $field['human_name']['#prefix'] = '<div class="label-input">';
            $field['human_name']['#suffix'] = '<div class="description">' . $type . '</div></div>';
          }
        }
      }

      // field group last type
      if (isset($form['fields']['_add_new_group']['format']['type']['#default_value'])) {
        $form['fields']['_add_new_group']['format']['type']['#default_value'] = variable_get('default_values:field_ui_display_overview_form:type', 'html-element');
      }

      $form['#submit'][] = 'seven_usability__form_submit';
      break;
  }
}

/**
 * form submit
 */
function seven_usability__form_submit($form, &$form_state) {
  $values = &$form_state['values'];

  switch ($form['#form_id']) {
    case 'node_type_form':
      // new node last settings
      variable_set('default_values:node_type_form:node_preview', $values['node_preview']);
      variable_set('default_values:node_type_form:title_label', $values['title_label']);
      variable_set('default_values:node_type_form:node_options', join(',', $values['node_options']));
      variable_set('default_values:node_type_form:node_submitted', $values['node_submitted']);
      variable_set('default_values:node_type_form:comment', $values['comment']);
      break;

    case 'field_ui_field_overview_form':
      // field group last type
      if (isset($values['fields']['_add_new_group']['format']['type'])) {
        variable_set('default_values:field_ui_field_overview_form:type', $values['fields']['_add_new_group']['format']['type']);
      }
      break;

    case 'field_ui_display_overview_form':
      // field group last type
      if (isset($values['fields']['_add_new_group']['format']['type'])) {
        variable_set('default_values:field_ui_display_overview_form:type', $values['fields']['_add_new_group']['format']['type']);
      }
      break;
  }
}

/**
 * Implements hook_ds_layout_info_alter().
 */
function seven_usability_ds_layout_info_alter(&$layouts) {
  // remove default ds layouts
  foreach ($layouts as $key => &$layout) {
    if (preg_match('/^ds_/', $key)) {
      unset($layouts[$key]);
    }
  }
}

/**
 * Implements hook_field_formatter_settings_form_alter().
 */
function seven_usability_field_formatter_settings_form_alter(&$element, &$form_state, $context) {
  // default class for ds extras theme expert
  if (isset($element['ft']['func']['#options'])) {
    $class_name = '';
    if (isset($form_state['field']['field_name'])) {
      $class_name = $form_state['field']['field_name'];
    }

    if (isset($form_state['field']['field_name'])) {
      $class_name = preg_replace('/^field_/', '', $form_state['field']['field_name']);
    }
    else {
      $class_name = $form_state['field']['name'];
    }
    $class_name = preg_replace('/_+/', '-', $class_name);

    if ($class_name == 'body') {
      $class_name = 'description';
    }

    if ($element['ft']['ow']['#default_value'] == FALSE && $element['ft']['ow-cl']['#default_value'] == '') {
      $element['ft']['ow-cl']['#default_value'] = 'wrap-' . $class_name;
    }
    if ($element['ft']['fi']['#default_value'] == FALSE && $element['ft']['fi-cl']['#default_value'] == '') {
      $element['ft']['fi-cl']['#default_value'] = $class_name;
    }
  }
}

/**
 * Implements hook_field_group_info_alter().
 */
function seven_usability_field_group_info_alter(&$groups) {
  /// remove base class
  foreach ($groups as $type => &$bundles) {
    foreach ($bundles as $bundle => &$views) {
      foreach ($views as $view => &$group_items) {
        foreach ($group_items as $group_name => &$group) {
          $class_names = &$group->format_settings['instance_settings']['classes'];

          $class_names = explode(' ', $class_names);
          $class_key = array_search('field-group-' . $group->format_type, $class_names);
          
          if ($class_key !== FALSE) {
            unset($class_names[$class_key]);
          }
          $class_names = join(' ', $class_names);
        }
      }
    }
  }
}
