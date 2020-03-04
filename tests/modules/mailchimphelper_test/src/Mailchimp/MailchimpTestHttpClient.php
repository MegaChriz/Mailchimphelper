<?php

namespace Mailchimp\Tests;

use Mailchimp\http\MailchimpHttpClientInterface;

/**
 * A dummy HTTP client used when running unit tests.
 * Does not make any real API requests.
 *
 * @package Mailchimp\Tests
 */
class MailchimpTestHttpClient implements MailchimpHttpClientInterface {

  public $method;

  public $uri;

  public $options;

  /**
   * @inheritdoc
   */
  public function handleRequest($method, $uri = '', $options = [], $parameters = [], $returnAssoc = FALSE) {
    if (!empty($parameters)) {
      if ($method == 'GET') {
        // Send parameters as query string parameters.
        $options['query'] = $parameters;
      }
      else {
        // Send parameters as JSON in request body.
        $options['json'] = (object) $parameters;
      }
    }

    $this->method = $method;
    $this->uri = $uri;
    $this->options = $options;

    // Save data in Drupal state as well.
    $requests = \Drupal::state()->get('mailchimp_requests', []);
    $requests[$uri][] = [
      'method' => $method,
      'uri' => $uri,
      'options' => $options,
    ];
    \Drupal::state()->set('mailchimp_requests', $requests);

    return new MailchimpTestHttpResponse();
  }

}
