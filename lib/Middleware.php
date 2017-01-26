<?php

namespace Hawk;

/**
 * Abstract class for middlewares
 */
abstract class Middleware {
	public $app;

	abstract function execute($req, $res);
}