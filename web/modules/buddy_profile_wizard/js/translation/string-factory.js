
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

    this.addString("math_game_title_2","How many people are in the house?","en");
    this.addString("math_game_title_2","Wieviele Leute sind im Haus?","de");

    this.addString("math_game_start","Start !","en");
    this.addString("math_game_start","Los !","de");

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
