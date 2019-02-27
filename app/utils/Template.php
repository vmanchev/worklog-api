<?php

namespace Worklog\Utils;

class Template
{

  /**
   * Parse templates and variables and return final result
   * 
   * The placeholders should not have spaces, e.g. "hello {{first_name}}" 
   * is correct and "hello {{ first_name }}" is incorrect.
   * 
   * Example: 
   * <pre>
   * //template content: Hello {{first_name}}!
   * $tpl = "~/app/templates/hello.html";
   * $params = ["first_name" => "John"];
   * $result = Toxic\Prodomio\Template::render($tpl, $params);
   * 
   * //will produce:
   * Hello John!
   * </pre>
   * 
   * @param string $tplFile Full path to the template file
   * @param array $params Associative array of parameters, which match 
   * the template placeholders without the brackets
   * @return String Parsed template
   */
  public static function renderHtml(\Phalcon\Mvc\View $view, string $tplFile, array $params = []) {

    return $view
            ->start()
            ->render($tplFile, $tplFile . '-html', $params)
            ->finish()
            ->getContent();
  }

  public static function renderTxt(\Phalcon\Mvc\View $view, string $tplFile, array $params = []) {

    return $view
            ->start()
            ->render($tplFile, $tplFile . '-txt', $params)
            ->finish()
            ->getContent();
  }

}
