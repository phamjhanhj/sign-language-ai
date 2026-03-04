<?php

$s = app(\App\Services\Firestore\DictionaryService::class);
$topics = $s->listTopics();
echo count($topics) . " topics\n";
foreach ($topics as $t) {
    $vocabs = $s->listVocabularies($t['cloud_id']);
    echo "  {$t['name']}: " . count($vocabs) . " vocabularies\n";
}
