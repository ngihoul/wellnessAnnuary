<?php

namespace App\Twig;

use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

use App\Repository\ServiceCategoryRepository;

class TwigExtension extends AbstractExtension
{
    private ServiceCategoryRepository $serviceCategoryRepository;
    private Environment $twig;
    private TagAwareAdapterInterface $cache;

    public function __construct(ServiceCategoryRepository $serviceCategoryRepository, Environment $twig, TagAwareAdapterInterface $cache) {
        $this->serviceCategoryRepository = $serviceCategoryRepository;
        $this->twig = $twig;
        $this->cache = $cache;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('subMenuCategory', [$this, 'getSubMenuCategory'], ['is_safe' => ['html']])
        ];
    }

    public function getSubMenuCategory(): string {
        return $this->cache->get('subMenuCategory', function(ItemInterface $item) {
            $item->tag(['category']);
            return $this->renderSubMenuCategory();
        });
    }

    public function renderSubMenuCategory(): string {
        $categories = $this->serviceCategoryRepository->findBy([], ['name' => 'ASC']);

        $arraySize = count($categories);
        $nbCategoriesPerColumn = 10;

        return $this->twig->render('fragments/_subMenuCategory.html.twig', [
            'categories' => $categories,
            'arraySize' => $arraySize,
            'nbCategoriesPerColumn' => $nbCategoriesPerColumn,
        ]);

    }
}