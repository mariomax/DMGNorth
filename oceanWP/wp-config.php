<?php

/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'dmgncom_wp123');

/** MySQL database username */
define('DB_USER', 'dmgncom_wp123');

/** MySQL database password */
define('DB_PASSWORD', ')8S7p)a2Lc');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8mb4');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'cfrsjwbvyf2jfkuor3qqunk1yd8k4lg7inamkf6rbjnihqnuuyrjkshptezpxhlq');
define('SECURE_AUTH_KEY',  '8yxscajw0o5dyugevzpnot3s8g2ddwofdcunzt0mbssawn8vrimqx8rcat0hq5wk');
define('LOGGED_IN_KEY',    'e6i7dkhb6qs4uodzkkbry8hb5d6bxmdlrafu2ciqjstoorq5jtgcma8lcrc7qjfi');
define('NONCE_KEY',        'beuaewvfx8fv4pcyrtoyy7oqeim8b1xen7wld6lr83areljjbegbn0uci7t0rqib');
define('AUTH_SALT',        'm864t8gzctn4zqpesi505raikday0bm6pzizvuiqqrahtp7bt7avvdvy4wwbgc3p');
define('SECURE_AUTH_SALT', 'rfavvvx49osns6viqwivytyxvg9e2t2pvloksbwsexr6o1sqkv0ay1bqq54nnd3l');
define('LOGGED_IN_SALT',   'y0cyf5dao9ueufsimnwbqmqhoxqeh8ndwdbsjdzoiepch3ecpg39dnnf3e8zo7lh');
define('NONCE_SALT',       'mdgwphgfzysaioro6y5u4ongtwpgqfakqr4up7cxhwgmrvu0e01iquya9i5qaibj');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wpx_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define('WP_DEBUG', false);

define('WP_CACHE', true); // Added by WP Hummingbird
/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');

/* Hummingbird page caching */
define('WP_CACHE', true);
