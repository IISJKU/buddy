
class TranslatedString{
  constructor(key) {
    this.key = key;
    this.en = null;
    this.de = null;
    this.sv = null;
  }

  addTranslation(text,lang){
    this[lang] = text;
  }

  getText(lang) {
    return this[lang];
  }
}

var stringFactory = {
  translatedStrings: [],


  init(lang = "en"){
    this.lang = lang;
    this.addString("intro_title","Hallo, willkommen beim Spiel!","de");
    this.addString("intro_title","Hello, welcome to the game!","en");
    this.addString("intro_start_game","Spiel starten","de");
    this.addString("intro_start_game","Start game","en");

    this.addString("math_game_title_1","Count the people in the house!","en");
    this.addString("math_game_title_1","Zähle die Leute im Haus","de");

    this.addString("math_game_title_2","Count the people in the house!","en");
    this.addString("math_game_title_2","Zähle die Leute im Haus!","de");

    this.addString("math_game_start","Start !","en");
    this.addString("math_game_start","Los !","de");

    this.addString("math_game_question","How many people are in the house?","en");
    this.addString("math_game_question","Wieviele Leute sind im Haus?","de");



    this.addString("reading_game_1_intro","Click on the word\n I am going to say. ","en");
    this.addString("reading_game_1_intro","Klick auf das Wort,\n dass ich gleich sagen werde.","de");


    this.addString("reading_game_tts_sentence_intro","Click on the sentence\n I am going to say. ","en");
    this.addString("reading_game_tts_sentence_intro","Klicke auf den Satz,\n den ich gleich sagen werde.","de");

    this.addString("reading_game_text_intro","Read the text and answer the question!","en");
    this.addString("reading_game_text_intro","Lies dir den Text durch\n und beantworte dann die Frage!","de");

    this.addString("memory_game_short_term_title","Remove duplicate\n item from the suitcase!","en");
    this.addString("memory_game_short_term_title","Entferne Dinge die\n sich bereits im Koffer befinden!","de");

    this.addString("memory_game_short_term_remove_item","Remove!","en");
    this.addString("memory_game_short_term_remove_item","Entfernen!","de");



  },

  addString(key,text, lang){

    for(let i=0; i < this.translatedStrings.length; i++){
      if(this.translatedStrings[i].key === key){
        this.translatedStrings[i].addTranslation(text,lang);
        return;
      }
    }

    let newTranslatedString = new TranslatedString(key);
    newTranslatedString.addTranslation(text,lang);
    this.translatedStrings.push(newTranslatedString);

  },

  getString(key,lang){
    if(!lang){
      lang = this.lang;
    }
    for(let i=0; i < this.translatedStrings.length; i++) {
      if (this.translatedStrings[i].key === key) {

        return this.translatedStrings[i].getText(lang);
      }
    }

  }
};

var gameLanguage = "de";
stringFactory.init(gameLanguage);


console.log(this.stringFactory);
