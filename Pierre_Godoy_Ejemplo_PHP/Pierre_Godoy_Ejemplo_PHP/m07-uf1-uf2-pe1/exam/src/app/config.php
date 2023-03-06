<?php
declare(strict_types=1);
namespace Config;



// ############################################################################
// About Constants
// ############################################################################

// Constants
// - const needs a constant value.
// - define() can take the result of an expression.
// - https://www.phptutorial.net/php-tutorial/php-constants/

// Namespaces + Constants
// - define() declares constants, but needs the Namespace prefix.
// - const declares constants inside the Namespace.
// - https://stackoverflow.com/questions/18247726/php-define-constants-inside-namespace-clarification

// PHP Intelliphense
// - PHP Intelliphense reports problems with the define() syntax, despite being correct. :(
// - It's a known bug: https://github.com/bmewburn/vscode-intelephense/issues/1684
// - Because of this, avoid the use constants...



// ############################################################################
// Project dirs
// ############################################################################

function get_project_dir(): string      { return realpath(__DIR__ . '/../..'); }

function get_db_dir(): string           { return get_project_dir() . '/db';     }
function get_context_dir(): string      { return get_project_dir() . '/db/context'; }

function get_public_dir(): string       { return get_project_dir() . '/public'; }
function get_source_dir(): string       { return get_project_dir() . '/src';    }

function get_app_dir(): string          { return get_project_dir() . '/src/app'; }
function get_lib_dir(): string          { return get_project_dir() . '/src/lib'; }

function get_controller_dir(): string   { return get_project_dir() . '/src/app/controller'; }
function get_model_dir(): string        { return get_project_dir() . '/src/app/model';      }
function get_view_dir(): string         { return get_project_dir() . '/src/app/view';       }


// ----------------------------------------------------------------------------
