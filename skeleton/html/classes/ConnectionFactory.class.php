<?php
// ConnectionFactory.class.php
// Copyright (c) 2010-2011 Ronald B. Cemer
// All rights reserved.
// This software is released under the BSD license.
// Please see the accompanying LICENSE.txt for details.

if (!class_exists('AbstractINIConnectionFactory', false)) include(dirname(__FILE__).'/dao/AbstractINIConnectionFactory.class.php');
if ((!defined('APP_ROOT_DIR')) || (!defined('APP_ROOT_URL')) || (!defined('APP_ROOT_URI'))) include dirname(dirname(__FILE__)).'/jax/include/appRoot.include.php';

class ConnectionFactory extends AbstractINIConnectionFactory {
}

ConnectionFactory::$INI_FILE = dirname(APP_ROOT_DIR).'/config/database.ini';
