class ReadingGameTTSWord extends QuizScene{
  constructor() {
    super("ReadingGameTTSWord");
  }

  preload() {
    super.preload();
    this.load.audio('highway', 'modules/buddy_profile_wizard/assets/sounds/memory_game/de/highway.mp3');
    this.load.audio('painter', 'modules/buddy_profile_wizard/assets/sounds/memory_game/de/painter.mp3');
    this.load.audio('rain', 'modules/buddy_profile_wizard/assets/sounds/memory_game/de/rain.mp3');
    this.load.audio('reading_intro', 'modules/buddy_profile_wizard/assets/sounds/reading_intro.mp3');


  }

  create(){

    let soundtest = new QuizQuestion("highway","", new Stimuli(null,"highway"));
    soundtest.addAnswer(new Answer(stringFactory.getString("reading_game_tts_word_question1_a1"),true));
    soundtest.addAnswer(new Answer(stringFactory.getString("reading_game_tts_word_question1_a2"),false));
    soundtest.addAnswer(new Answer(stringFactory.getString("reading_game_tts_word_question1_a3"),false));
    soundtest.addAnswer(new Answer(stringFactory.getString("reading_game_tts_word_question1_a3"),false));
    soundtest.addAnswer(new Answer(stringFactory.getString("reading_game_tts_word_question1_a4"),false));
    this.addQuestion(soundtest);


    soundtest = new QuizQuestion("painter","", new Stimuli(null,"painter"));
    soundtest.addAnswer(new Answer(stringFactory.getString("reading_game_tts_word_question2_a1"),true));
    soundtest.addAnswer(new Answer(stringFactory.getString("reading_game_tts_word_question2_a2"),false));
    soundtest.addAnswer(new Answer(stringFactory.getString("reading_game_tts_word_question2_a3"),false));
    soundtest.addAnswer(new Answer(stringFactory.getString("reading_game_tts_word_question2_a3"),false));
    soundtest.addAnswer(new Answer(stringFactory.getString("reading_game_tts_word_question2_a4"),false));
    this.addQuestion(soundtest);

    soundtest = new QuizQuestion("rain","", new Stimuli(null,"rain"));
    soundtest.addAnswer(new Answer(stringFactory.getString("reading_game_tts_word_question3_a1"),true));
    soundtest.addAnswer(new Answer(stringFactory.getString("reading_game_tts_word_question3_a2"),false));
    soundtest.addAnswer(new Answer(stringFactory.getString("reading_game_tts_word_question3_a3"),false));
    soundtest.addAnswer(new Answer(stringFactory.getString("reading_game_tts_word_question3_a3"),false));
    soundtest.addAnswer(new Answer(stringFactory.getString("reading_game_tts_word_question3_a4"),false));
    this.addQuestion(soundtest);


    /*
    let test = new QuizQuestion("banane","Die Bannane ist gelb?");
    test.addAnswer(new Answer("Ja",true));
    test.addAnswer(new Answer("Nein",false));
    this.addQuestion(test);

    test = new QuizQuestion("bier","Bier schmeckt gut?");
    test.addAnswer(new Answer("Ja",true));
    test.addAnswer(new Answer("Nein",false));
    this.addQuestion(test);

    */





    this.createTitle(stringFactory.getString("reading_game_1_intro"));
    let readingGame = this;
    this.startButton = new IconButton(this,stringFactory.getString("math_game_start"),this.cameras.main.centerX, 300,"playIcon",function (){

      readingGame.startGame();
    });


    this.avatarButton = new AvatarAudioButton(this,"reading_intro",this.cameras.main.centerX, 180,function (){

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

    Director.changeScene("ReadingGameTTSWord");
  }
}
