<?php

/*-------------------------------------------------------+
| Enzyme
| Copyright 2010-2011 Danny Allen <danny@enzyme-project.org>
| http://www.enzyme-project.org/
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/


class Developer {
  public $data                  = null;

  public static $fieldSections  = array('core'            => array('account', 'name', 'email', 'nickname', 'dob', 'gender', 'motivation', 'employer', 'colour'),
                                        'geographic'      => array('continent', 'country', 'location', 'latitude', 'longitude'),
                                        'social'          => array('homepage', 'blog', 'lastfm', 'microblog_type', 'microblog_user'),
                                        'system'          => array('access_ip', 'access_code', 'access_timeout'));

  // type:      datatype (string, float, enum)
  // display:   where the field is displayed ('all', 'admin', 'hidden')
  // editable:  whether this value can be changed within Enzyme
  public static $fields         = array('account'         => array('type'     => 'string',
                                                                   'display'  => 'all',
                                                                   'editable' => false),
                                        'name'            => array('type'     => 'string',
                                                                   'display'  => 'all',
                                                                   'editable' => true),
                                        'email'           => array('type'     => 'string',
                                                                   'display'  => 'all',
                                                                   'editable' => true),
                                        'nickname'        => array('type'     => 'string',
                                                                   'display'  => 'all',
                                                                   'editable' => true),
                                        'dob'             => array('type'     => 'date',
                                                                   'display'  => 'all',
                                                                   'editable' => true),
                                        'gender'          => array('type'     => 'enum',
                                                                   'display'  => 'all',
                                                                   'editable' => true),
                                        'motivation'      => array('type'     => 'enum',
                                                                   'display'  => 'all',
                                                                   'editable' => true),
                                        'employer'        => array('type'     => 'string',
                                                                   'display'  => 'all',
                                                                   'editable' => true),
                                        'colour'          => array('type'     => 'enum',
                                                                   'display'  => 'all',
                                                                   'editable' => true),

                                        'continent'       => array('type'     => 'enum',
                                                                   'display'  => 'all',
                                                                   'editable' => true),
                                        'country'         => array('type'     => 'string',
                                                                   'display'  => 'all',
                                                                   'editable' => true),
                                        'location'        => array('type'     => 'string',
                                                                   'display'  => 'all',
                                                                   'editable' => true),
                                        'latitude'        => array('type'     => 'float',
                                                                   'display'  => 'all',
                                                                   'editable' => true),
                                        'longitude'       => array('type'     => 'float',
                                                                   'display'  => 'all',
                                                                   'editable' => true),

                                        'homepage'        => array('type'     => 'string',
                                                                   'display'  => 'all',
                                                                   'editable' => true),
                                        'blog'            => array('type'     => 'string',
                                                                   'display'  => 'all',
                                                                   'editable' => true),
                                        'lastfm'          => array('type'     => 'string',
                                                                   'display'  => 'all',
                                                                   'editable' => true),
                                        'microblog_type'  => array('type'     => 'enum',
                                                                   'display'  => 'all',
                                                                   'editable' => true),
                                        'microblog_user'  => array('type'     => 'string',
                                                                   'display'  => 'all',
                                                                   'editable' => true));


  public function __construct($value = null, $field = 'account') {
    // load in constructor?
    if ($value) {
      $this->load($value, $field);
    }
  }


  public function load($value = null, $field = 'account') {
    if (!$value) {
      if (!isset($this->data['account'])) {
        return false;
      }

      $field = 'account';
      $value = $this->data['account'];
    }

    // load developer data
    $this->data = Db::load('developers', array($field => $value), 1);

    // stop if no developer data found
    if (!$this->data) {
      return false;
    }

    // if loading by access_code, ensure code has not expired
    if (empty($this->data['access_timeout']) || (time() > strtotime($this->data['access_timeout']))) {
      $this->data = null;
      return false;
    }

    // return successful load
    return true;
  }


  public function save() {
//    if (!isset($this->data['account'])) {
//      return false;
//    }
//
//    // serialise arrays as strings for storage
//    if (!empty($this->paths)) {
//      $this->data['paths']        = App::combineCommaList($this->paths);
//    }
//    if (!empty($this->permissions)) {
//      $this->data['permissions']  = App::combineCommaList($this->permissions);
//    }
//
//    // save changes in database
//    return Db::save('developers', array('account' => $this->data['account']), $this->data);
  }


  public static function getFieldStrings() {
    $fields  = array('account'        => _('Account'),
                     'name'           => _('Name'),
                     'email'          => _('Email'),
                     'nickname'       => _('Nickname'),
                     'dob'            => _('DOB'),
                     'gender'         => _('Gender'),
                     'motivation'     => _('Motivation'),
                     'employer'       => _('Employer'),
                     'colour'         => _('Colour'),

                     'continent'      => _('Continent'),
                     'country'        => _('Country'),
                     'location'       => _('Location'),
                     'latitude'       => _('Latitude'),
                     'longitude'      => _('Longitude'),

                     'homepage'       => _('Homepage URL'),
                     'blog'           => _('Blog URL'),
                     'lastfm'         => _('Last.fm username'),
                     'microblog_type' => _('Microblog service'),
                     'microblog_user' => _('Microblog username'));

    return $fields;
  }


  // context can be:
  //  - 'all'
  //  - 'category'
  //  - 'key' (default)
  public static function enumToString($context = 'key', $key = null) {
    $keys                   = array();

    // map enums to i18n strings
    $keys['gender']         = array('male'            => _('Male'),
                                    'female'          => _('Female'));

    $keys['motivation']     = array('volunteer'       => _('Volunteer'),
                                    'commercial'      => _('Commercial'));

    $keys['colour']         = array('red'             => _('Red'),
                                    'blue'            => _('Blue'),
                                    'green'           => _('Green'),
                                    'black'           => _('Black'),
                                    'yellow'          => _('Yellow'),
                                    'purple'          => _('Purple'),
                                    'brown'           => _('Brown'),
                                    'grey'            => _('Grey'),
                                    'orange'          => _('Orange'),
                                    'pink'            => _('Pink'),
                                    'white'           => _('White'));

    $keys['continent']      = array('europe'          => _('Europe'),
                                    'africa'          => _('Africa'),
                                    'asia'            => _('Asia'),
                                    'oceania'         => _('Oceania'),
                                    'north-america'   => _('North America'),
                                    'south-america'   => _('South America'));

    $keys['microblog_type'] = array('twitter'         => _('twitter.com'),
                                    'identica'        => _('identi.ca'));

    // return...
    if ($context == 'all') {
      // return all
      return $keys;

    } else if ($context == 'category') {
      // return a whole category
      if (isset($keys[$key])) {
        return $keys[$key];
      }

    } else if ($key) {
      // return a single key
      foreach ($keys as $section) {
        if (isset($section[$key])) {
          return $section[$key];
        }
      }

      return $key;
    }

    return false;
  }
}

?>