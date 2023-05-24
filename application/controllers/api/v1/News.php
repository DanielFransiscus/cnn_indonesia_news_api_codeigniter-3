<?php
defined('BASEPATH') or exit('No direct script access allowed');

use chriskacerguis\RestServer\RestController;
use GuzzleHttp\Client;

class News extends RestController
{
  public function getNews($url, $category)
  {
    $client = new Client();
    $response = $client->request('GET', $url);
    $body = $response->getBody()->getContents();
    $xml = new SimpleXMLElement($body);
    $items = $xml->channel->item;
    $data = [];

    foreach ($items as $item) {
      $data[] = [
        'title' => (string)$item->title,
        'pubdate' => (string)$item->pubDate,
        'link' => (string)$item->link,
        'image' => (string)$item->enclosure['url'],
        'category' => $category
      ];
    }

    return [
      'total_results' => count($data),
      'data' => $data
    ];
  }

  public function index_get()
  {
    $segment4 = $this->uri->segment(4);

    // Validasi URI segment keempat
    $validSegments = ['nasional', 'internasional', 'olahraga', 'lifestyles', 'teknologi', 'hiburan', 'search'];
    if ($segment4 && !in_array($segment4, $validSegments)) {
      return $this->notFoundResponse('Endpoint not found');
    }

    $url = 'https://www.cnnindonesia.com/rss';
    $data = $this->getNews($url, 'Berita Terbaru, Terkini Indonesia, Dunia');

    if (empty($data['data'])) {
      return $this->notFoundResponse('Data not found');
    }

    return $this->successResponse($data);
  }

  public function teknologi_get()
  {
    $url = 'https://www.cnnindonesia.com/teknologi/rss';
    $data = $this->getNews($url, 'Berita Terkini Teknologi');

    if (empty($data['data'])) {
      return $this->notFoundResponse('Data not found');
    }

    return $this->successResponse($data);
  }

  public function nasional_get()
  {
    $url = 'https://www.cnnindonesia.com/nasional/rss';
    $data = $this->getNews($url, 'Berita Terkini Nasional');

    if (empty($data['data'])) {
      return $this->notFoundResponse('Data not found');
    }

    return $this->successResponse($data);
  }

  public function internasional_get()
  {
    $url = 'https://www.cnnindonesia.com/internasional/rss';
    $data = $this->getNews($url, 'Berita Terkini Internasional');

    if (empty($data['data'])) {
      return $this->notFoundResponse('Data not found');
    }

    return $this->successResponse($data);
  }

  public function hiburan_get()
  {
    $url = 'https://www.cnnindonesia.com/hiburan/rss';
    $data = $this->getNews($url, 'Berita Terkini Hiburan');

    if (empty($data['data'])) {
      return $this->notFoundResponse('Data not found');
    }

    return $this->successResponse($data);
  }

  public function lifestyles_get()
  {
    $url = 'https://www.cnnindonesia.com/gaya-hidup/rss';
    $data = $this->getNews($url, 'Berita Terkini Gaya Hidup');

    if (empty($data['data'])) {
      return $this->notFoundResponse('Data not found');
    }

    return $this->successResponse($data);
  }

  public function olahraga_get()
  {
    $url = 'https://www.cnnindonesia.com/olahraga/rss';
    $data = $this->getNews($url, 'Berita Terkini Olahraga');

    if (empty($data['data'])) {
      return $this->notFoundResponse('Data not found');
    }

    return $this->successResponse($data);
  }

  public function category_get($category)
  {
    $validCategories = ['nasional', 'internasional', 'olahraga', 'lifestyles', 'teknologi', 'hiburan'];
    if (!in_array($category, $validCategories)) {
      return $this->notFoundResponse('Category not found');
    }

    $url = 'https://www.cnnindonesia.com/' . $category . '/rss';
    $data = $this->getNews($url, $category);

    if (empty($data['data'])) {
      return $this->notFoundResponse('Data not found');
    }

    return $this->successResponse($data);
  }

  public function search_get()
  {
    $query = $this->input->get('q');
    $urls = [
      'https://www.cnnindonesia.com/rss',
      'https://www.cnnindonesia.com/nasional/rss',
      'https://www.cnnindonesia.com/internasional/rss',
      'https://www.cnnindonesia.com/olahraga/rss',
      'https://www.cnnindonesia.com/gaya-hidup/rss',
      'https://www.cnnindonesia.com/teknologi/rss',
      'https://www.cnnindonesia.com/hiburan/rss'
    ];
    $data = [];
    $resultCount = 0;

    foreach ($urls as $url) {
      $category = $this->getCategoryFromUrl($url);
      $newsData = $this->getNews($url, $category);
      foreach ($newsData['data'] as $item) {
        if (stripos($item['title'], $query) !== false) {
          $item['category'] = $category; // Menambahkan informasi kategori
          $data[] = $item;
          $resultCount++;
        }
      }
    }

    return $this->successResponse(['total_results' => $resultCount, 'data' => $data]);
  }

  private function getCategoryFromUrl($url)
  {
    $categories = [
      'https://www.cnnindonesia.com/nasional/rss' => 'Berita Terkini Nasional',
      'https://www.cnnindonesia.com/internasional/rss' => 'Berita Terkini Internasional',
      'https://www.cnnindonesia.com/olahraga/rss' => 'Berita Terkini Olahraga',
      'https://www.cnnindonesia.com/gaya-hidup/rss' => 'Berita Terkini Gaya Hidup',
      'https://www.cnnindonesia.com/teknologi/rss' => 'Berita Terkini Teknologi',
      'https://www.cnnindonesia.com/hiburan/rss' => 'Berita Terkini Hiburan',
      'https://www.cnnindonesia.com/rss' => 'Berita Terbaru, Terkini Indonesia, Dunia'
    ];

    return $categories[$url] ?? '';
  }

  private function notFoundResponse($message)
  {
    return $this->response([
      'code' => 404,
      'status' => 'Not Found',
      'message' => $message
    ], 404);
  }

  private function successResponse($data)
  {
    return $this->response([
      'code' => 200,
      'status' => 'OK',
      'total_results' => $data['total_results'],
      'data' => $data['data']
    ], 200);
  }
}
