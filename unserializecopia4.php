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
    // $locais_unserialized[$i] = unserialize($local);
    
    // if ( isset($locais_unserialized[$i])){
        
		
		// for ( $j=0; isset($locais_unserialized[$i][$j]); $j++) {
            // if ( !is_null($locais_unserialized[$i][$j]) ){
                // $new = array();
                // $old = $locais_unserialized[$i][$j];
                // $new["locais_".$j."_nome_do_local"] = isset($old['local']) ? $old['local'] : "";
                // $new["locais_".$j."_endereco"]      = isset($old['endereco']) ? $old['endereco'] : "";
                // $new["locais_".$j."_cidade"]        = isset($old['cidade']) ? $old['cidade'] : "";
                // $new["locais_".$j."_cep"]           = isset($old['cep']) ? $old['cep'] : "";
                // $new["locais_".$j."_telefone_1"]    = isset($old['telefone_1']) ? $old['telefone_1'] : "";
                // $new["locais_".$j."_telefone_2"]    = isset($old['telefone_2']) ? $old['telefone_2'] : "";
                // $new["locais_".$j."_email"]         = isset($old['email']) ? $old['email'] : "";
                // $new["locais_".$j."_site"]         = isset($old['site']) ? $old['site'] : "";
                // $new["locais_".$j."_observacoes"]   = isset($old['observacoes']) ? $old['observacoes'] : "";
				
				
				
				
				
                // for ( $k=0; isset($old['horarios'][$k]['dias']) and !is_null($old['horarios'][$k]['dias']); $k++) {
                    // $quantidade_locais = count($old['horarios'][$k]['dias']);
                    // $keys_locais = array_keys($old['horarios'][$k]['dias']);
                    // $locais_serialized = "a:$quantidade_locais:{";
                    // for ($a=0; $a < $quantidade_locais; $a++){
                        // $locais_serialized .= "i:$a;s:3:\"$keys_locais[$a]\";";
                    // }
                    // $locais_serialized .= "}";
                    // $new["locais_".$j."_dias_da_semana__horario"] = $k+1;
                    // $new["locais_".$j."_dias_da_semana__horario_".$k."_dias"] = $locais_serialized;
                    // $new["locais_".$j."_dias_da_semana__horario_".$k."_hora_i"] = $old['horarios'][$k]['hora_i'];
                    // $new["locais_".$j."_dias_da_semana__horario_".$k."_hora_f"] = $old['horarios'][$k]['hora_f'];
                    // $unserialized[$i] = $new;
                    // unset($locais_serialized);
                // }
            // }
            // $new["nome_do_evento"]= isset($old['nome']) ? $old['nome'] : "";
				// $new["data_inicial"]= isset($old['data_inicio']) ? $old['data_inicio'] : "";
				// $new["data_final"]= isset($old['data_fim']) ? $old['data_fim'] : "";
				// $new["classificacao_indicativa_"]= isset($old['classificacao']) ? $old['classificacao'] : "";
				// $new["preco_"]= isset($old['preco']) ? $old['preco'] : "";
            // $unserialized[$i]['locais'] = $j+1;
        // }
    // }
    
    foreach ($locais as $i => $local) {
        if (isset($local)) { // Verifica se o local atual não é nulo
            $new = array();
            $new["locais_{$i}_nome_do_local"] = $local['local'] ?? "";
            $new["locais_{$i}_endereco"]      = $local['endereco'] ?? "";
            $new["locais_{$i}_cidade"]        = $local['cidade'] ?? "";
            $new["locais_{$i}_cep"]           = $local['cep'] ?? "";
            $new["locais_{$i}_telefone_1"]    = $local['telefone_1'] ?? "";
            $new["locais_{$i}_telefone_2"]    = $local['telefone_2'] ?? "";
            $new["locais_{$i}_email"]         = $local['email'] ?? "";
            $new["locais_{$i}_site"]          = $local['site'] ?? "";
            $new["locais_{$i}_observacoes"]   = $local['observacoes'] ?? "";

            // Processa os horários diretamente, sem serialização
            if (!empty($local['horarios'])) {
                foreach ($local['horarios'] as $k => $horario) {
                    $new["locais_{$i}_dias_da_semana__horario"] = $k + 1;
                    $new["locais_{$i}_dias_da_semana__horario_{$k}_dias"] = implode(',', array_keys($horario['dias'] ?? []));
                    $new["locais_{$i}_dias_da_semana__horario_{$k}_hora_i"] = $horario['hora_i'] ?? "";
                    $new["locais_{$i}_dias_da_semana__horario_{$k}_hora_f"] = $horario['hora_f'] ?? "";
                }
            }

            // Adiciona informações gerais do evento
            $new["informacoes_i_nome_do_evento"] = $local['nome'] ?? "";
            $new["informacoes_i_data_inicial"] = $local['data_inicio'] ?? "";
            $new["informacoes_0_data_final"] = $local['data_fim'] ?? "";
            $new["classificacao_0_indicativa_"] = $local['classificacao'] ?? "";
            $new["preco_"] = $local['preco'] ?? "";

            // Adiciona os dados processados ao array final
            $unserialized[$i] = $new;
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
    $current_data = isset($unserialized[$i]) ? $unserialized[$i] : [];
    if (!empty($current_data)) {
        $keys = array_keys($current_data);
        foreach ( $keys as $key ) {
            //$postmeta = $item->addChild('postmeta');
            $postmeta = $item->addChild('postmeta', null, $namespace_wp);
            $postmeta->addChildWithCData('meta_key', $key, $namespace_wp);
            $postmeta->addChildWithCData('meta_value', $unserialized[$i][$key], $namespace_wp);
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