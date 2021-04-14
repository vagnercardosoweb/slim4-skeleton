<?php

/*
 * Vagner Cardoso <https://github.com/vagnercardosoweb>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 14/04/2021 Vagner Cardoso
 */

namespace Core\Support;

/**
 * Class Paginator.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class Paginator
{
    /**
     * @var int
     */
    protected int $totalRows;

    /**
     * @var int
     */
    protected int $limit;

    /**
     * @var int
     */
    protected int $offset;

    /**
     * @var int
     */
    protected int $totalPages;

    /**
     * @var int
     */
    protected int $rangePages;

    /**
     * @var string
     */
    protected string $link;

    /**
     * @var int
     */
    protected int $currentPage;

    /**
     * @param int $totalRows
     * @param int $limit
     * @param string $link
     * @param int $rangePages
     * @param string $pageString
     */
    public function __construct(int $totalRows, string $link, int $limit = 10, int $rangePages = 4, string $pageString = 'page')
    {
        // Attributes
        $this->totalRows = $totalRows;
        $this->link = $link;
        $this->limit = $limit ?: 10;
        $this->rangePages = $rangePages ?: 4;

        // Calculates total pages
        $this->totalPages = max((int)ceil($this->totalRows / $this->limit), 1);

        // Filter page
        $currentPage = filter_input(INPUT_GET, $pageString, FILTER_DEFAULT);
        $currentPage = ($currentPage > PHP_INT_MAX) ? $this->totalPages : $currentPage;
        $this->currentPage = (int)($currentPage ?? 1);

        // Calculate offset
        $this->offset = ($this->currentPage * $this->limit) - $this->limit;

        // Mount o link
        if (str_contains($this->link, '?')) {
            $this->link = "{$this->link}&{$pageString}=";
        } else {
            $this->link = "{$this->link}?{$pageString}=";
        }

        // Check the page total passed
        if (($this->totalRows > 0 && $this->offset > 0) && ($this->offset >= $this->totalRows)) {
            header("Location: {$this->link}{$this->totalPages}", true, 301);
            exit;
        }
    }

    /**
     * @return string
     */
    public function toJson(): string
    {
        return json_encode($this->toArray());
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'totalRows' => $this->getTotalRows(),
            'limit' => $this->getLimit(),
            'offset' => $this->getOffset(),
            'totalPages' => $this->getTotalPages(),
            'rangePages' => $this->getRangePages(),
            'prevPage' => $this->getPrevPage(),
            'nextPage' => $this->getNextPage(),
            'currentPage' => $this->getCurrentPage(),
            'currentPageFirstItem' => $this->getCurrentPageFirstItem(),
            'currentPageLastItem' => $this->getCurrentPageLastItem(),
            'items' => $this->getItems(),
        ];
    }

    /**
     * @return int
     */
    public function getTotalRows(): int
    {
        return $this->totalRows;
    }

    /**
     * @return int
     */
    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * @return int
     */
    public function getOffset(): int
    {
        return $this->offset;
    }

    /**
     * @return int
     */
    public function getTotalPages(): int
    {
        return $this->totalPages;
    }

    /**
     * @return int
     */
    public function getRangePages(): int
    {
        return $this->rangePages;
    }

    /**
     * @return string|null
     */
    public function getPrevPage(): ?string
    {
        if ($this->currentPage > 1) {
            $prevPage = $this->currentPage - 1;

            return "{$this->link}{$prevPage}";
        }

        return null;
    }

    /**
     * @return string|null
     */
    public function getNextPage(): ?string
    {
        if ($this->totalPages > $this->currentPage) {
            $nextPage = $this->currentPage + 1;

            return "{$this->link}{$nextPage}";
        }

        return null;
    }

    /**
     * @return int
     */
    public function getCurrentPage(): int
    {
        return $this->currentPage;
    }

    /**
     * @return int|null
     */
    public function getCurrentPageFirstItem(): ?int
    {
        $first = ($this->currentPage - 1) * $this->limit + 1;

        return $first <= $this->totalRows
            ? (int)$first
            : null;
    }

    /**
     * @return int|null
     */
    public function getCurrentPageLastItem(): ?int
    {
        if (!$first = $this->getCurrentPageFirstItem()) {
            return null;
        }

        $last = $first + $this->limit - 1;

        return $last > $this->totalRows
            ? $this->totalRows
            : (int)$last;
    }

    /**
     * @return array
     */
    public function getItems(): array
    {
        $items = [];

        if ($this->getTotalPages() <= 1) {
            return $items;
        }

        if ($this->getTotalPages() <= $this->getRangePages()) {
            for ($i = 1; $i <= $this->getTotalPages(); $i++) {
                $items[] = $this->createItem($i, $this->getCurrentPage() == $i);
            }
        } else {
            $startPage = ($this->getCurrentPage() - $this->getRangePages()) > 0
                ? $this->getCurrentPage() - $this->getRangePages()
                : 1;

            $endPage = ($this->getCurrentPage() + $this->getRangePages()) < $this->getTotalPages()
                ? $this->getCurrentPage() + $this->getRangePages()
                : $this->getTotalPages();

            if ($startPage > 1) {
                $items[] = $this->createItem(1, 1 == $this->getCurrentPage());
                $items[] = $this->createItem();
            }

            for ($i = $startPage; $i <= $endPage; $i++) {
                $items[] = $this->createItem($i, $this->getCurrentPage() == $i);
            }

            if ($endPage < $this->getTotalPages()) {
                $items[] = $this->createItem();
                $items[] = $this->createItem($this->getTotalPages(), $this->getCurrentPage() == $this->getTotalPages());
            }
        }

        return $items;
    }

    /**
     * @return string
     */
    public function getLink(): string
    {
        return $this->link;
    }

    /**
     * @param string $class
     *
     * @return string|null
     */
    public function toHtml(string $class = 'pagination'): ?string
    {
        if ($this->getTotalPages() <= 1) {
            return null;
        }

        $html = "<ul class='{$class}'>";

        foreach ($this->getItems() as $item) {
            if (!empty($item['pattern'])) {
                $html .= sprintf(
                    "<li class='%s-item %s'><a href='%s'>%s</a></li>",
                    htmlspecialchars($class),
                    htmlspecialchars($item['current'] ? 'active' : ''),
                    htmlspecialchars($item['pattern']),
                    htmlspecialchars($item['number'])
                );
            } else {
                $html .= sprintf(
                    "<li class='%s-item ellipsis'><span>%s</span></li>",
                    htmlspecialchars($class),
                    htmlspecialchars($item['number'])
                );
            }
        }

        $html .= '</ul>';

        return $html;
    }

    /**
     * @param int $number
     * @param bool $current
     *
     * @return array
     */
    protected function createItem(int $number = 0, bool $current = false): array
    {
        return [
            'number' => ($number > 0 ? $number : '...'),
            'pattern' => ($number > 0 ? "{$this->getLink()}{$number}" : false),
            'current' => $current,
        ];
    }
}
