<?php
namespace Helpers;

/**
 * @Created 15.05.2020 21:46:48
 * @Project simpleFramework
 * @Author Mehmet Emre Tülek <memretulek@gmail.com>
 * @Class Pagination
 * @package Helpers
 * @property-read int $start
 * @property-read int $limit
 */

class Pagination {

    const PATTERN = "{num}";

    public array $language = [
        'prev' => 'Önceki',
        'next' => 'Sonraki',
        'first' => 'İlk Sayfa',
        'last' => 'Son Sayfa'
    ];


    public int $totalRecord = 0;
    public int $totalPage;
    public int $perPage;
    public $currentPage;

    public int $start;
    public int $limit;

    public bool $prevNext = true;
    public bool $firstLast = true;
    public int $maxNavigationItems = 4;
    public int $jumpNavigationItems = 10;


    private string $urlPattern;
    private array $items = [];
    private int $itemNumber = 0;

    public function __construct(int $totalRecord, int $currentPage = 1, int $perPage = 20, string $urlPattern = "?page={num}")
    {
        $this->totalRecord = $totalRecord;
        $this->perPage = $perPage ? $perPage : 1;
        $this->totalPage = ceil(($this->totalRecord / $this->perPage));
        $this->totalPage = $this->totalPage ? $this->totalPage : 1;
        $this->currentPage = $currentPage > 0 ? $currentPage : 1;
        $this->currentPage = $this->currentPage < $this->totalPage ? $this->currentPage : $this->totalPage;
        $this->limit = $this->perPage;
        $this->start = ($this->currentPage - 1) * $this->limit;
        $this->urlPattern = $urlPattern;
    }


    /**
     * @return mixed
     */
    private function firstLast()
    {
        $item['first'] = [
            'pageNum' => 1,
            'text' => $this->language['first'],
            'is_current' => $this->currentPage == 1,
        ];

        $item['last'] = [
            'pageNum' => $this->totalPage,
            'text' => $this->language['last'],
            'is_current' => $this->currentPage == $this->totalPage
        ];

        return $item;
    }

    /**
     * @return mixed
     */
    private function prevNext()
    {
        $prevPageNum = $this->currentPage > 1 ? $this->currentPage - 1 : 1;
        $nextPageNum = $this->currentPage < $this->totalPage ? $this->currentPage + 1 : $this->totalPage;

        $item['prev'] = [
            'pageNum' => $prevPageNum,
            'text' => $this->language['prev'],
            'is_current' => $this->currentPage == 1,
            'url' => $prevPageNum
        ];

        $item['next'] = [
            'pageNum' => $nextPageNum,
            'text' => $this->language['next'],
            'is_current' => $this->currentPage == $this->totalPage,
            'url' => $nextPageNum
        ];

        return $item;
    }

    /**
     * @param $pageNum
     * @param $text
     * @param $is_current
     * @param $is_disable
     * @param null $itemName
     */
    private function addItem($pageNum, $text, $is_current, $is_disable, $itemName = null)
    {
        $itemName = is_string($itemName) ? $itemName : $this->itemNumber++;
        $this->items[$itemName] = (object) [
            'pageNum' => $pageNum,
            'text' => $text,
            'is_current' => $is_current,
            'is_disable' => $is_disable,
            'url' => $pageNum ? $this->parseUrl($pageNum) : '#'
        ];
    }

    /**
     * @param $pageNum
     * @return string|string[]
     */
    private function parseUrl($pageNum)
    {
        return str_replace(self::PATTERN, $pageNum, $this->urlPattern);
    }

    /**
     * @param bool $shown_only
     * @return array
     */
    public function buildPaginate($shown_only = false)
    {
        if($this->firstLast && $this->currentPage != 1){
            $item = $this->firstLast();
            $this->addItem($item['first']['pageNum'], $item['first']['text'], $item['first']['is_current'],  $item['first']['is_current'],'first');
        }
        if($this->prevNext && $this->currentPage != 1){
            $item = $this->prevNext();
            $this->addItem($item['prev']['pageNum'], $item['prev']['text'], $item['prev']['is_current'], $item['prev']['is_current'],'prev');
        }


        for($i = 1; $i < $this->totalPage + 1; $i++){

            if($shown_only) {
                $left = $this->currentPage - ceil(($this->maxNavigationItems + 1) / 2);
                $right = $this->currentPage + ceil(($this->maxNavigationItems + 1) / 2);

                if ($left >= $i) {

                    if ($this->jumpNavigationItems && $left == $i+$this->jumpNavigationItems-1) {
                        $this->addItem($i, $i, false, false);
                    }
                    if ($left == $i) {
                        $this->addItem(null, '...', false, true);
                    }
                    continue;
                }
                if ($right <= $i) {

                    if ($this->jumpNavigationItems && $right == $i-$this->jumpNavigationItems+1) {
                        $this->addItem($i, $i, false, false);
                    }
                    if ($right == $i) {
                        $this->addItem(null, '...', false, true);
                    }
                    continue;
                }
            }

            $current = $this->currentPage == $i;
            $this->addItem($i, $i, $current, false);
        }


        if($this->prevNext && $this->currentPage != $this->totalPage){
            $item = $this->prevNext();
            $this->addItem($item['next']['pageNum'], $item['next']['text'], $item['next']['is_current'], $item['next']['is_current'],'next');
        }
        if($this->firstLast && $this->currentPage != $this->totalPage){
            $item = $this->firstLast();
            $this->addItem($item['last']['pageNum'], $item['last']['text'], $item['last']['is_current'], $item['last']['is_current'],'last');
        }

        return $this->items;
    }

    /**
     * @return string
     */
    public function navigation()
    {
        $this->buildPaginate(true);

        $navigate = '<ul class="pagination">';
        foreach($this->items as $item){
            $current = $item->is_current ? ' active ':'';
            $disabled = $item->is_disable ? ' disabled ':'';
            $navigate .= '<li class="page-item '.$current.$disabled.' "><a class="page-link" href="'.$item->url.'">'.$item->text.'</a>';
        }
        $navigate .= '</ul>';

        return $navigate;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->navigation();
    }
}
