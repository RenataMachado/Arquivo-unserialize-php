<?php

// import .xml file
$file = 'campos-velhos-05-12-.2024.xml';
$wordpress_xml = file_get_contents($file);


// find all the posts in the xml file
preg_match_all('/<item>(.*?)<\/item>/s', $wordpress_xml, $posts);
//$posts = $xml->xpath('//item');
//print_r($posts[1]);

// find the postmeta tags that contain the key locais
$locais[] = array();
preg_match_all('/<wp:postmeta>(.*?)<\/wp:postmeta>/s', $wordpress_xml, $postmeta);
if (count($postmeta[1]) > 0) {
    foreach ($postmeta[1] as $meta) {
        preg_match('/<wp:meta_key><!\[CDATA\[(.*?)\]\]><\/wp:meta_key>/', $meta, $key);
        preg_match('/<wp:meta_value><!\[CDATA\[(.*?)\]\]><\/wp:meta_value>/', $meta, $value);
		/*echo "Chave: " . $key[1] . " - Valor: " . $value[1] . "\n"; // Exibir chave e valor*/
        if ($key[1] == 'locais') {
            $locais[] = $value[1];
        }
    }
}


// unserialize the data except the 'dias' array, formatting the data to the new standard
$i = 0;
$unserialized[] = array();
foreach ($locais as $local) {
    if ($i == 0) {
        $i++;
        continue;
    }

    $locais_unserialized[$i] = unserialize($local);

    if (isset($locais_unserialized[$i])) {
        for ($j = 0; isset($locais_unserialized[$i][$j]); $j++) {
            if (!is_null($locais_unserialized[$i][$j])) {
                $new = array();
                $old = $locais_unserialized[$i][$j];

                // Mapear os dados para as meta keys do XML
                $new["_locais_{$j}_nome_do_local"] = $old['local'] ?? "";
                $new["_locais_{$j}_endereco"] = $old['endereco'] ?? "";
                $new["_locais_{$j}_cidade"] = $old['cidade'] ?? "";
                $new["_locais_{$j}_cep"] = $old['cep'] ?? "";
                $new["_locais_{$j}_telefone_1"] = $old['telefone_1'] ?? "";
                $new["_locais_{$j}_telefone_2"] = $old['telefone_2'] ?? "";
                $new["_locais_{$j}_email"] = $old['email'] ?? "";
                $new["_locais_{$j}_site"] = $old['site'] ?? "";
                $new["_locais_{$j}_observacoes"] = $old['observacoes'] ?? "";
                #$new["locais_{$j}"]   = $local["{$ii}"] ?? "";
		

                // Processar os horários
                for ($k = 0; isset($old['horarios'][$k]['dias']) and !is_null($old['horarios'][$k]['dias']); $k++) {
                    $dias = $old['horarios'][$k]['dias'];
                    $dias_serialized = serialize(array_keys($dias));

                    $new["_locais_{$j}_dias_da_semana__horario_{$k}_dias"] = $dias_serialized;
                    $new["_locais_{$j}_dias_da_semana__horario_{$k}_hora_i"] = $old['horarios'][$k]['hora_i'] ?? "";
                    $new["_locais_{$j}_dias_da_semana__horario_{$k}_hora_f"] = $old['horarios'][$k]['hora_f'] ?? "";
                }

                $unserialized[$i] = $new;
            }
        }
    }
    $i++;
}

unset($informacoes_unserialized);
//print_r($unserialized);
class MySimpleXMLElement extends SimpleXMLElement{
    public function addChildWithCData($name , $value, $namespace) {
        $new = parent::addChild($name, null, $namespace);
        $base = dom_import_simplexml($new);
        $docOwner = $base->ownerDocument;
        $base->appendChild($docOwner->createCDATASection($value));
    }
}
$xml = new MySimpleXMLElement($wordpress_xml);

$namespaces = $xml->getDocNamespaces();
$namespace_wp = $namespaces['wp'];


// substitute the data on the xml file
foreach ($xml->channel->item as $item) {
    foreach ($item->children($namespace_wp) as $index => $postmeta){
        foreach ($postmeta->children($namespace_wp) as $meta) {
            if ($meta->getName() == 'meta_key' && $meta->__toString() == 'locais'){
                unset($postmeta[0]);
                break 2; // Break out of the inner and outer loops
            }
        }
    }
}
$i = 1;
foreach ($xml->channel->item as $item) {
    // Remove a meta_key antiga "locais"
    foreach ($item->children($namespace_wp) as $postmeta) {
        foreach ($postmeta->children($namespace_wp) as $meta) {
            if ($meta->getName() == 'meta_key' && $meta->__toString() == 'locais') {
                unset($postmeta[0]); // Remove a entrada antiga
                break 2; // Sai do loop
            }
        }
    }

    // Adiciona os novos metadados ao XML
    $current_data = $unserialized[$i] ?? [];
    if (!empty($current_data)) {
        foreach ($current_data as $key => $value) {
            // Adiciona cada meta key ao XML
            $postmeta = $item->addChild('postmeta', null, $namespace_wp);
            $postmeta->addChildWithCData('meta_key', $key, $namespace_wp);
            $postmeta->addChildWithCData('meta_value', $value, $namespace_wp);

            // Adiciona a versão visível sem o prefixo "_", se necessário
            if (strpos($key, '_locais_') === 0) {
                $visible_key = ltrim($key, '_'); // Remove o prefixo "_"
                $postmeta = $item->addChild('postmeta', null, $namespace_wp);
                $postmeta->addChildWithCData('meta_key', $visible_key, $namespace_wp);
                $postmeta->addChildWithCData('meta_value', $value, $namespace_wp);
            }
        }
    }
    $i++;
}

/*
// convert tags and categories to one-liners
foreach ($xml->channel->item as $item) {
    $category = array();
    $tag = array();
    foreach($item->category as $content){
        if ( str_contains($content->asXML(), 'domain="post_tag"') ){
            $tag[] = $content->__toString();
        }
        else {
            if ( str_contains($content->asXML(), 'domain="category"') ){
                $category[] = $content->__toString();
            }
        }
    }
    $item->addChild('tags', implode(',',$tag));
    $item->addChild('cat_extracted', implode(',',$category));
}
//*/
//echo $xml->asXML();
$xml->asXML('05-12-2024-noticia-gerado.xml');

?>