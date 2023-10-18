<?php

namespace Twig\Loader;

if (!interface_exists('\Twig\Loader\ExistsLoaderInterface')) {
  interface ExistsLoaderInterface {}
}

if (!interface_exists('\Twig\Loader\SourceContextLoaderInterface')) {
  interface SourceContextLoaderInterface {}
}
