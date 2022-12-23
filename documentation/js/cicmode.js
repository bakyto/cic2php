/* Определение режима cic */

CodeMirror.defineSimpleMode("cicmode", {
  // Начальное состояние содержит правила, которые изначально используются
  start: [
	// {{container}}
	//{regex: /\{\{+[-0-9._\[\]()a-zA-Z]+\}\}/, token: "atom"},
    //{regex: /<cic.[^>]*type=("xml"|'xml')[^>]*>/, token: "meta", mode: {spec: "xml", end: /<\/cic>/}},
	//{regex: /<cic.[^>]*type=("sql"|'sql')[^>]*>/, token: "meta", mode: {spec: "sql", end: /<\/cic>/}},
	//{regex: /<cic.[^>]*type=("json"|'json')[^>]*>/, token: "meta", mode: {spec: "javascript", end: /<\/cic>/}},
	//{regex: /<cic.[^>]*>/, token: "meta", dedentIfLineStart:true, mode: {spec: "xml", end: /<\/cic>/}},
	//{regex: /<loop.[^>]*>/, token: "meta", mode: {spec: "xml", end: /<\/loop>/}},
	//{regex: /<</, token: "meta", mode: {spec: "xml", end: />>/}}
	//{regex: /\{\{+[-0-9._\[\]()a-zA-Z]+\}\}/, token: "atom"},
	{mode: {spec: "htmlmixed"}}	
	
  ]
});
