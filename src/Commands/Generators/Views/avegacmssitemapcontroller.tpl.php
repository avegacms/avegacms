<@php
declare(strict_types=1);

namespace {namespace};

use AvegaCms\Utilities\SeoUtils;
use CodeIgniter\Controller;    
use ReflectionException;

class Sitemap extends Controller
{
    /**
     * @param  string  $pointer
     * @return void
     * @throws ReflectionException
     */
    public static function run(string $pointer = ''): void
    {
        $sitemap = [
            'content'       => [],
            'content.pages' => []
        ];

        if (empty($pointer)) {
            foreach ($sitemap as $key => $data) {
                SeoUtils::sitemap($key, $data);
            }
        } elseif (array_key_exists($pointer, $sitemap)) {
            SeoUtils::sitemap($pointer, $sitemap[$pointer]);
        }
    }
}
