<?php

/**
 * Build or rebuild the translation index from all the source files
 * and templates on the site.
 */

$this->require_admin ();

$page->layout = 'admin';

if (! isset ($this->params[0])) {
	if (! file_exists ('lang/_index.php')) {
		$page->title = i18n_get ('Building index');
	} else {
		$page->title = i18n_get ('Rebuilding index');
	}

	echo 'Please wait...';
	$page->add_script ('<meta http-equiv="refresh" content="0;url=/translator/build/running" />');
	return;
}

// Build the index
$sources = array (
	'layouts/*.html',
	'layouts/*/*.html',
	'apps/*/views/*.html',
	'apps/*/views/*/*.html',
	'apps/*/views/*/*/*.html',
	'apps/*/handlers/*.php',
	'apps/*/handlers/*/*.php',
	'apps/*/handlers/*/*/*.php',
	'apps/*/lib/*.php',
	'apps/*/models/*.php',
	'install/*.php',
	'install/layouts/*.html'
);
if (file_exists ('lang/_index.php')) {
	$list = unserialize (file_get_contents ('lang/_index.php'));
} else {
	$list = array ();
}

set_time_limit (90);

foreach ($sources as $source) {
	$files = glob ($source);
	$files = is_array ($files) ? $files : array ();
	foreach ($files as $file) {
		$data = file_get_contents ($file);
		if (preg_match ('/\.html/', $file)) {
			// parse for {""} syntax
			preg_match_all ('/\{[\'"] ?(.*?) ?[\'"]\}/', $data, $matches);
			foreach ($matches[1] as $str) {
				if (! isset ($list[$str])) {
					$list[$str] = array (
						'orig' => $str,
						'src' => $file
					);
				}
			}
		} else {
			// parse for i18n_getf?() syntax
			preg_match_all ('/(i18n_getf?|__) ?\([\'"](.*?)[\'"]\)/', $data, $matches);
			foreach ($matches[2] as $str) {
				$str = stripslashes ($str);
				if (! isset ($list[$str])) {
					$list[$str] = array (
						'orig' => $str,
						'src' => $file
					);
				}
			}

			// parse for I18n::export syntax
			preg_match_all ('/I18n::export\s+?\(([^\)]+)\)/s', $data, $matches);
			foreach ($matches[1] as $match) {
				if (! preg_match ('/array\s+?\(/', $match)) {
					$match = 'array (' . $match;
				}
				$match = '<?php $__tmp__ = ' . $match . ');?>';

				$tokens = token_get_all ($match);
				foreach ($tokens as $tok) {
					if ($tok[0] === T_CONSTANT_ENCAPSED_STRING) {
						$str = stripslashes (trim ($tok[1], '"\''));
						$list[$str] = array (
							'orig' => $str,
							'src' => $file
						);
					}
				}
			}
		}
	}
}
asort ($list);
file_put_contents ('lang/_index.php', serialize ($list));
chmod ('lang/_index.php', 0666);

$page->title = i18n_get ('Indexing completed');

echo '<p><a href="/translator/index">' . i18n_get ('Continue') . '</a></p>';

?>