class ReadingGameTTSSentence extends QuizScene{
  constructor() {
    super("ReadingGameTTSSentence");
  }

  preload() {
    super.preload();
    this.load.audio('intro', 'modules/buddy_profile_wizard/assets/sounds/reading_game_tts_sentence/de/intro.mp3');
    this.load.audio('day', 'modules/buddy_profile_wizard/assets/sounds/reading_game_tts_sentence/de/day.mp3');
    this.load.audio('glory', 'modules/buddy_profile_wizard/assets/sounds/reading_game_tts_sentence/de/glory.mp3');
    this.load.audio('football', 'modules/buddy_profile_wizard/assets/sounds/reading_game_tts_sentence/de/football.mp3');
    this.load.audio('spiders', 'modules/buddy_profile_wizard/assets/sounds/reading_game_tts_sentence/de/spiders.mp3');


  }

  create(){

    let soundtest = new QuizQuestion("day","", new Stimuli(null,"day"));
    soundtest.columnLayout = 2;
    soundtest.addAnswer(new Answer("Heute ist\n ein schöner Tag!",true));
    soundtest.addAnswer(new Answer("Heute regnet\n es!",false));
    soundtest.addAnswer(new Answer("Heuer ist\n es kalt!",false));
    soundtest.addAnswer(new Answer("Heute wird\n es regnen!",false));
    this.addQuestion(soundtest);


    soundtest = new QuizQuestion("glory","", new Stimuli(null,"glory"));
    soundtest.columnLayout = 2;
    soundtest.addAnswer(new Answer("Die glorreichen\n Sieben!",true));
    soundtest.addAnswer(new Answer("Der Chor schreit\n im Liegen!",false));
    soundtest.addAnswer(new Answer("Die Moorleichen\n fliegen!",false));
    soundtest.addAnswer(new Answer("Das Ohr zeigt\n nach Süden!",false));
    soundtest.addAnswer(new Answer("Ein Tor reicht\n zum Siegen!",false));
    this.addQuestion(soundtest);

    soundtest = new QuizQuestion("football","", new Stimuli(null,"football"));
    soundtest.columnLayout = 2;
    soundtest.addAnswer(new Answer("Ich spiele\n gerne Fussball!",true));
    soundtest.addAnswer(new Answer("Ich spiele\n gerne Völkerball!",false));
    soundtest.addAnswer(new Answer("Ich spiehle\n gehrne Fusbal!",false));
    soundtest.addAnswer(new Answer("Ich spile\n gerne Fusbal!",false));
    soundtest.addAnswer(new Answer("Ich spielle\n gehrne Fussbal!",false));
    this.addQuestion(soundtest);


    soundtest = new QuizQuestion("spiders","", new Stimuli(null,"spiders"));
    soundtest.columnLayout = 2;
    soundtest.addAnswer(new Answer("Spinnen sind meine\n Lieblingstiere!",true));
    soundtest.addAnswer(new Answer("Spihnnen sind meine\n Lieblingstiere!",false));
    soundtest.addAnswer(new Answer("Spinnen sind meine\n Lieblingsthere!",false));
    soundtest.addAnswer(new Answer("Spiinnen sind maine\n Lieblingstiere!",false));
    soundtest.addAnswer(new Answer("Spinnen sind maine\n Lieblingstire!",false));
    this.addQuestion(soundtest);


    this.createTitle(stringFactory.getString("reading_game_tts_sentence_intro"));
    let readingGame = this;
    this.startButton = new IconButton(this,stringFactory.getString("math_game_start"),this.cameras.main.centerX, 300,"playIcon",function (){

      readingGame.startGame();
    });


    this.avatarButton = new AvatarAudioButton(this,"intro",this.cameras.main.centerX, 180,function (){

    });
    this.avatarButton.init();
    this.add.existing(this.avatarButton);
    this.startButton.init();
    this.add.existing(this.startButton);



  }

  startGame(){
    this.titleText.destroy();
    this.avatarButton.destroy();
    this.startButton.destroy();
    this.showNextQuestion();
  }


  questionFinishedHook(id,answer){
    console.log("Q finished",id,answer);

    return super.questionFinishedHook(id,answer);

  }
  quizFinishedHook(){

    console.log("starting bla");
    Director.changeScene("ReadingGameTTSSentence");
  }


}
