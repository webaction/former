<?php
/**
 * Form
 *
 * Construct and manages the form wrapping all fields
 */
namespace Former;

use \Underscore\Arrays;
use \Underscore\String;

class Form extends Traits\FormerObject
{
  /**
   * Illuminate application instance.
   * @var Illuminate/Foundation/Application
   */
  protected $app;

  /**
   * The Form type
   * @var string
   */
  public $type = null;

  /**
   * The available form types
   * @var array
   */
  private $availableTypes = array('horizontal', 'vertical', 'inline', 'search');

  /**
   * The destination of the current form
   * @var string
   */
  private $action;

  /**
   * The form method
   * @var string
   */
  private $method;

  /**
   * Whether the form should be secured or not
   * @var boolean
   */
  private $secure;

  /**
   * Whether the current form is opened or not
   * @var boolean
   */
  private $opened = false;

  public function __construct($app)
  {
    $this->app = $app;
  }

  /**
   * Opens up magically a form
   *
   * @param  string $typeAsked  The form type asked
   * @param  array  $parameters Parameters passed
   * @return string             A form opening tag
   */
  public function open($typeAsked, $parameters)
  {
    $action     = Arrays::get($parameters, 0);
    $method     = Arrays::get($parameters, 1, 'POST');
    $attributes = Arrays::get($parameters, 2, array());
    $secure     = Arrays::get($parameters, 3, false);

    // If classic form
    if($typeAsked == 'open') $type = $this->app['config']->get('former::default_form_type');
    else {
      // Look for HTTPS form
      if (String::contains($typeAsked, 'secure')) {
        $typeAsked = String::remove($typeAsked, 'secure');
        $secure = true;
      }

      // Look for file form
      if (String::contains($typeAsked, 'for_files')) {
        $typeAsked = String::remove($typeAsked, 'for_files');
        $attributes['enctype'] = 'multipart/form-data';
      }

      // Calculate form type
      $type = String::remove($typeAsked, 'open');
      $type = trim($type, '_');
      if(!in_array($type, $this->availableTypes)) $type = $this->app['config']->get('former::default_form_type');
    }

    // Add the final form type
    $attributes = $this->app['former.helpers']->addClass($attributes, 'form-'.$type);

    // Store it
    $this->type = $type;

    // Fetch errors if asked for
    if ($this->app['config']->get('former::fetch_errors')) {
      $this->app['former']->withErrors();
    }

    // Open the form
    $this->action     = $action;
    $this->method     = $method;
    $this->attributes = $attributes;
    $this->secure     = $secure;

    return $this;
  }

  /**
   * Closes a Form
   *
   * @return string A closing <form> tag
   */
  public function close()
  {
    return '</form>';
  }

  ////////////////////////////////////////////////////////////////////
  /////////////////////////////// HELPERS ////////////////////////////
  ////////////////////////////////////////////////////////////////////

  public function isOpened()
  {
    return $this->opened;

    return $this;
  }

  ////////////////////////////////////////////////////////////////////
  ////////////////////////// CHAINED METHODS /////////////////////////
  ////////////////////////////////////////////////////////////////////

  /**
   * Change the form's action
   *
   * @param  string $action The new action
   */
  public function action($action)
  {
    $this->action = $action;

    return $this;
  }

  /**
   * Change the form's method
   *
   * @param  string $method The method to use
   */
  public function method($method)
  {
    $this->method = $method;

    return $this;
  }

  /**
   * Whether the form should be secure
   *
   * @param  boolean $secure Secure or not
   */
  public function secure($secure = true)
  {
    $this->secure = $secure;

    return $this;
  }

  /**
   * Alias for $this->app['former']->withRules
   *
   * @param array $rules Rules
   */
  public function rules()
  {
    call_user_func_array(array($this->app['former'], 'withRules'), func_get_args());

    return $this;
  }

  /**
   * Outputs the current form opened
   *
   * @return string A <form> opening tag
   */
  public function __toString()
  {
    // Mark the form as opened
    $this->opened = true;

    return $this->app['former.laravel.form']->open($this->action, $this->method, $this->attributes, $this->secure);
  }
}
