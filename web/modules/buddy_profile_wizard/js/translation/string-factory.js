
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
    this.addString("yes","Yes","en");
    this.addString("yes","Ja ","de");
    this.addString("no","No","en");
    this.addString("no","Nein ","de");

    this.addString("math_game_title_1","Count the people in the house!","en");
    this.addString("math_game_title_1","Zähle die Leute im Haus","de");

    this.addString("math_game_title_2","Count the people in the house!","en");
    this.addString("math_game_title_2","Zähle die Leute im Haus!","de");

    this.addString("math_game_start","Start !","en");
    this.addString("math_game_start","Los !","de");

    this.addString("math_game_question","How many people are in the house?","en");
    this.addString("math_game_question","Wieviele Leute sind im Haus?","de");

    this.addString("reading_game_1_intro","Click on the word\n I am going to say. ","en");
    this.addString("reading_game_1_intro","Klick auf das Wort,\n das ich gleich sagen werde.","de");
    //Q1
    this.addString("reading_game_tts_word_question1_a1","Highway","en");
    this.addString("reading_game_tts_word_question1_a1","Autobahn","de");
    this.addString("reading_game_tts_word_question1_a2","Highscool","en");
    this.addString("reading_game_tts_word_question1_a2","Autobus","de");
    this.addString("reading_game_tts_word_question1_a3","Eyeball","en");
    this.addString("reading_game_tts_word_question1_a3","Kameramann","de");
    this.addString("reading_game_tts_word_question1_a4","Highboard","en");
    this.addString("reading_game_tts_word_question1_a4","Kameramann","de");
    this.addString("reading_game_tts_word_question1_a5","Chicken","en");
    this.addString("reading_game_tts_word_question1_a5","Wienerschnitzel","de");

    //Q2
    this.addString("reading_game_tts_word_question2_a1","Painter","en");
    this.addString("reading_game_tts_word_question2_a1","Maler","de");
    this.addString("reading_game_tts_word_question2_a2","Pain","en");
    this.addString("reading_game_tts_word_question2_a2","Taler","de");
    this.addString("reading_game_tts_word_question2_a3","Gain","en");
    this.addString("reading_game_tts_word_question2_a3","Senf","de");
    this.addString("reading_game_tts_word_question2_a4","Panther","en");
    this.addString("reading_game_tts_word_question2_a4","Kraft","de");
    this.addString("reading_game_tts_word_question2_a5","Power","en");
    this.addString("reading_game_tts_word_question2_a5","Müller","de");

    //Q3
    this.addString("reading_game_tts_word_question3_a1","Rain","en");
    this.addString("reading_game_tts_word_question3_a1","Regen","de");
    this.addString("reading_game_tts_word_question3_a2","Ray","en");
    this.addString("reading_game_tts_word_question3_a2","Degen","de");
    this.addString("reading_game_tts_word_question3_a3","Brain","en");
    this.addString("reading_game_tts_word_question3_a3","Segen","de");
    this.addString("reading_game_tts_word_question3_a4","Power","en");
    this.addString("reading_game_tts_word_question3_a4","Franz","de");
    this.addString("reading_game_tts_word_question3_a5","Rock","en");
    this.addString("reading_game_tts_word_question3_a5","Baum","de");

    //Reading Game TTS TEXT
    this.addString("reading_game_tts_sentence_intro","Click on the sentence\n I am going to say. ","en");
    this.addString("reading_game_tts_sentence_intro","Klicke auf den Satz,\n den ich gleich sagen werde.","de");

      //Q1
    this.addString("reading_game_tts_sentence_question1_a1","Today is a\n nice day!","en");
    this.addString("reading_game_tts_sentence_question1_a1","Heute ist\n ein schöner Tag!","de");
    this.addString("reading_game_tts_sentence_question1_a2","Today it\n is raining!","en");
    this.addString("reading_game_tts_sentence_question1_a2","Heute regnet\n es!","de");
    this.addString("reading_game_tts_sentence_question1_a3","Today is a\n cold day!","en");
    this.addString("reading_game_tts_sentence_question1_a3","Heute ist\n es kalt!","de");
    this.addString("reading_game_tts_sentence_question1_a4","Today it will\n be raining!","en");
    this.addString("reading_game_tts_sentence_question1_a4","Heute wird\n es regnen!","de");

      //Q2
    this.addString("reading_game_tts_sentence_question2_a1","I like to eat pizza!","en");
    this.addString("reading_game_tts_sentence_question2_a1","Ich esse gerne Pizza!","de");
    this.addString("reading_game_tts_sentence_question2_a2","I love to eat pizza!","en");
    this.addString("reading_game_tts_sentence_question2_a2","Ich liebe es Pizza zu essen!","de");
    this.addString("reading_game_tts_sentence_question2_a3","I like tomatoes!","en");
    this.addString("reading_game_tts_sentence_question2_a3","Ich mag Tomaten!","de");
    this.addString("reading_game_tts_sentence_question2_a4","I like to eat bread!","en");
    this.addString("reading_game_tts_sentence_question2_a4","Ich esse gerne Brot!","de");

      //Q3
    this.addString("reading_game_tts_sentence_question3_a1","I like to\n play football!","en");
    this.addString("reading_game_tts_sentence_question3_a1","Ich spiele\n gerne Fussball!","de");
    this.addString("reading_game_tts_sentence_question3_a2","I lieke to\n play fotball!","en");
    this.addString("reading_game_tts_sentence_question3_a2","Ich spielee\n gernee Fusbal!","de");
    this.addString("reading_game_tts_sentence_question3_a3","I like to\n play fodbaal!","en");
    this.addString("reading_game_tts_sentence_question3_a3","Ich spiehle\n gehrne Fusbal!","de");
    this.addString("reading_game_tts_sentence_question3_a4","I licke too\n play footbal!","en");
    this.addString("reading_game_tts_sentence_question3_a4","Ich spielle\n gehrne Fussbal!","de");


      //Q4
    this.addString("reading_game_tts_sentence_question4_a1","Spiders are my\n favorite animals!","en");
    this.addString("reading_game_tts_sentence_question4_a1","Spinnen sind meine\n Lieblingstiere!","de");
    this.addString("reading_game_tts_sentence_question4_a2","Spiiders are my\n faforite animals","en");
    this.addString("reading_game_tts_sentence_question4_a2","Spihnnen sind meine\n Lieblingstiere!","de");
    this.addString("reading_game_tts_sentence_question4_a3","Spiders are mi\n favoorite animals!","en");
    this.addString("reading_game_tts_sentence_question4_a3","Spinnen sind meine\n Lieblingsthere!","de");
    this.addString("reading_game_tts_sentence_question4_a4","Spedirs are my\n vavorite aanimals!","en");
    this.addString("reading_game_tts_sentence_question4_a4","Spinnen sind maine\n Lieblingstire!","de");


    //READING GAME TEXT
    this.addString("reading_game_text_intro","Read the text\n and answer the question!","en");
    this.addString("reading_game_text_intro","Lies dir den Text durch\n und beantworte dann die Frage!","de");
    this.addString("reading_game_text_question1","A banana is yellow!","en");
    this.addString("reading_game_text_question1","Die Banane ist gelb?","de");
    this.addString("reading_game_text_question2","Milk can be drunk?","en");
    this.addString("reading_game_text_question2","Kann man Milch trinken? ","de");

    this.addString("memory_game_short_term_title","Remove duplicate\n items from the suitcase!","en");
    this.addString("memory_game_short_term_title","Entferne Dinge die\n sich bereits im Koffer befinden!","de");

    this.addString("memory_game_short_term_remove_item","Remove!","en");
    this.addString("memory_game_short_term_remove_item","Entfernen!","de");


    this.addString("memory_game_short_term_remove_item","Remove!","en");
    this.addString("memory_game_short_term_remove_item","Entfernen!","de");


    //Focus game text
    this.addString("reading_game_text_intro", "Find the coin after the cup\n stopped moving!","en");
    this.addString("reading_game_text_intro","Finde die Münze nachdem sich die\n Becher nicht mehr bewegen!","de");


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

var gameLanguage = "en";
stringFactory.init(gameLanguage);


console.log(this.stringFactory);
