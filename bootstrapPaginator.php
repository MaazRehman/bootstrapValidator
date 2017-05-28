<?php

/**
 * Class Paginator
 */
class bootstrapPaginator{

    const NUM_PLACEHOLDER = '(:num)';
    protected $iTotalItems;
    protected $iNumberOfPages;
    protected $iItemsPerPage;
    protected $iCurrentPage;
    protected $sUrlPattern;
    protected $iMaxPageToShow = 10;
    protected $previousText = 'Previous';
    protected $nextText = 'Next';

    /**
     * @param int $totalItems The total number of items.
     * @param int $itemsPerPage The number of items per page.
     * @param int $currentPage The current page number.
     * @param string $urlPattern A URL for each page, with (:num) as a placeholder for the page number. Ex. '/foo/page/(:num)'
     */
    public function __construct($totalItems, $itemsPerPage, $currentPage, $urlPattern = '')
    {
        $this->iTotalItems = $totalItems;
        $this->iItemsPerPage = $itemsPerPage;
        $this->iCurrentPage = $currentPage;
        $this->sUrlPattern = $urlPattern;

        $this->updateNumPages();
    }

    /** Fascade To make paginate
     * @param $aArray
     * @param $itemsPerPage
     * @param $currentPage
     * @param string $urlPattern
     * @return bootstrapPaginator
     */
    public static function paginate($aArray, $itemsPerPage, $currentPage, $urlPattern = ''){
        $totalItems = count($aArray);


        $oPaginator = new bootstrapPaginator($totalItems, $itemsPerPage, $currentPage, $urlPattern);
    return $oPaginator;
    }

    /**
     *
     */
    protected function updateNumPages()
    {
        $this->iNumberOfPages = ($this->iItemsPerPage == 0 ? 0 : (int) ceil($this->iTotalItems/$this->iItemsPerPage));
    }

    /**
     * @param int $iMaxPageToShow
     * @throws \InvalidArgumentException if $maxPagesToShow is less than 3.
     */
    public function setIMaxPageToShow($iMaxPageToShow)
    {
        if ($iMaxPageToShow < 3) {
            throw new \InvalidArgumentException('maxPagesToShow cannot be less than 3.');
        }
        $this->iMaxPageToShow = $iMaxPageToShow;
    }

    /**
     * @return int
     */
    public function getIMaxPageToShow()
    {
        return $this->iMaxPageToShow;
    }

    /**
     * @param int $iCurrentPage
     */
    public function setICurrentPage($iCurrentPage)
    {
        $this->iCurrentPage = $iCurrentPage;
    }

    /**
     * @return int
     */
    public function getICurrentPage()
    {
        return $this->iCurrentPage;
    }

    /**
     * @param int $iItemsPerPage
     */
    public function setIItemsPerPage($iItemsPerPage)
    {
        $this->iItemsPerPage = $iItemsPerPage;
        $this->updateNumPages();
    }

    /**
     * @return int
     */
    public function getIItemsPerPage()
    {
        return $this->iItemsPerPage;
    }

    /**
     * @param int $iTotalItems
     */
    public function setITotalItems($iTotalItems)
    {
        $this->iTotalItems = $iTotalItems;
        $this->updateNumPages();
    }

    /**
     * @return int
     */
    public function getITotalItems()
    {
        return $this->iTotalItems;
    }

    /**
     * @return int
     */
    public function getNumPages()
    {
        return $this->iNumberOfPages;
    }

    /**
     * @param string $sUrlPattern
     */
    public function setSUrlPattern($sUrlPattern)
    {
        $this->sUrlPattern = $sUrlPattern;
    }

    /**
     * @return string
     */
    public function getSUrlPattern()
    {
        return $this->sUrlPattern;
    }

    /**
     * @param int $pageNum
     * @return string
     */
    public function getPageUrl($pageNum)
    {
        return str_replace(self::NUM_PLACEHOLDER, $pageNum, $this->sUrlPattern);
    }

    /**
     * @return int|null
     */
    public function getNextPage()
    {
        if ($this->iCurrentPage < $this->iNumberOfPages) {
            return $this->iCurrentPage + 1;
        }

        return null;
    }

    /**
     * @return int|null
     */
    public function getPrevPage()
    {
        if ($this->iCurrentPage > 1) {
            return $this->iCurrentPage - 1;
        }

        return null;
    }

    /**
     * @return null|string
     */
    public function getNextUrl()
    {
        if (!$this->getNextPage()) {
            return null;
        }

        return $this->getPageUrl($this->getNextPage());
    }

    /**
     * @return string|null
     */
    public function getPrevUrl()
    {
        if (!$this->getPrevPage()) {
            return null;
        }

        return $this->getPageUrl($this->getPrevPage());
    }

    /**
     * Get an array of paginated page data.
     *
     * Example:
     * array(
     *     array ('num' => 1,     'url' => '/example/page/1',  'isCurrent' => false),
     *     array ('num' => '...', 'url' => NULL,               'isCurrent' => false),
     *     array ('num' => 3,     'url' => '/example/page/3',  'isCurrent' => false),
     *     array ('num' => 4,     'url' => '/example/page/4',  'isCurrent' => true ),
     *     array ('num' => 5,     'url' => '/example/page/5',  'isCurrent' => false),
     *     array ('num' => '...', 'url' => NULL,               'isCurrent' => false),
     *     array ('num' => 10,    'url' => '/example/page/10', 'isCurrent' => false),
     * )
     *
     * @return array
     */
    public function getPages()
    {
        $pages = array();

        if ($this->iNumberOfPages <= 1) {
            return array();
        }

        if ($this->iNumberOfPages <= $this->iMaxPageToShow) {
            for ($i = 1; $i <= $this->iNumberOfPages; $i++) {
                $pages[] = $this->createPage($i, $i == $this->iCurrentPage);
            }
        } else {

            // Determine the sliding range, centered around the current page.
            $numAdjacents = (int) floor(($this->iMaxPageToShow - 3) / 2);

            if ($this->iCurrentPage + $numAdjacents > $this->iNumberOfPages) {
                $slidingStart = $this->iNumberOfPages - $this->iMaxPageToShow + 2;
            } else {
                $slidingStart = $this->iCurrentPage - $numAdjacents;
            }
            if ($slidingStart < 2) $slidingStart = 2;

            $slidingEnd = $slidingStart + $this->iMaxPageToShow - 3;
            if ($slidingEnd >= $this->iNumberOfPages) $slidingEnd = $this->iNumberOfPages - 1;

            // Build the list of pages.
            $pages[] = $this->createPage(1, $this->iCurrentPage == 1);
            if ($slidingStart > 2) {
                $pages[] = $this->createPageEllipsis();
            }
            for ($i = $slidingStart; $i <= $slidingEnd; $i++) {
                $pages[] = $this->createPage($i, $i == $this->iCurrentPage);
            }
            if ($slidingEnd < $this->iNumberOfPages - 1) {
                $pages[] = $this->createPageEllipsis();
            }
            $pages[] = $this->createPage($this->iNumberOfPages, $this->iCurrentPage == $this->iNumberOfPages);
        }


        return $pages;
    }


    /**
     * Create a page data structure.
     *
     * @param int $pageNum
     * @param bool $isCurrent
     * @return Array
     */
    protected function createPage($pageNum, $isCurrent = false)
    {
        return array(
            'num' => $pageNum,
            'url' => $this->getPageUrl($pageNum),
            'isCurrent' => $isCurrent,
        );
    }

    /**
     * @return array
     */
    protected function createPageEllipsis()
    {
        return array(
            'num' => '...',
            'url' => null,
            'isCurrent' => false,
        );
    }

    /**
     * Render an HTML pagination control.
     *
     * @return string
     */
    public function toHtml()
    {
        if ($this->iNumberOfPages <= 1) {
            return '';
        }

        $html = '<ul class="pagination">';
        if ($this->getPrevUrl()) {
            $html .= '<li><a href="' . $this->getPrevUrl() . '">&laquo; '. $this->previousText .'</a></li>';
        }

        foreach ($this->getPages() as $page) {
            if ($page['url']) {
                $html .= '<li' . ($page['isCurrent'] ? ' class="active"' : '') . '><a href="' . $page['url'] . '">' . $page['num'] . '</a></li>';
            } else {
                $html .= '<li class="disabled"><span>' . $page['num'] . '</span></li>';
            }
        }

        if ($this->getNextUrl()) {
            $html .= '<li><a href="' . $this->getNextUrl() . '">'. $this->nextText .' &raquo;</a></li>';
        }
        $html .= '</ul>';

        return $html;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->toHtml();
    }

    /**
     * @return int|null
     */
    public function getCurrentPageFirstItem()
    {
        $first = ($this->iCurrentPage - 1) * $this->iItemsPerPage + 1;

        if ($first > $this->iTotalItems) {
            return null;
        }

        return $first;
    }

    /**
     * @return int|null
     */
    public function getCurrentPageLastItem()
    {
        $first = $this->getCurrentPageFirstItem();
        if ($first === null) {
            return null;
        }

        $last = $first + $this->iItemsPerPage - 1;
        if ($last > $this->iTotalItems) {
            return $this->iTotalItems;
        }

        return $last;
    }

    /**
     * @param $text
     * @return $this
     */
    public function setPreviousText($text)
    {
        $this->previousText = $text;
        return $this;
    }

    /**
     * @param $text
     * @return $this
     */
    public function setNextText($text)
    {
        $this->nextText = $text;
        return $this;
    }
}