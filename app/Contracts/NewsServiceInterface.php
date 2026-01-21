<?php

namespace App\Contracts;

interface NewsServiceInterface
{
    /**
     * Fetch articles from the news source.
     *
     * @param array $params
     * @return array
     */
    public function fetchArticles(array $params = []): array;

    /**
     * Get the news source name.
     *
     * @return string
     */
    public function getSourceName(): string;

    /**
     * Transform the article data.
     *
     * @param array $rawArticle
     * @return array
     */
    public function transformArticle(array $rawArticle): array;
}
