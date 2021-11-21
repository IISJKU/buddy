class UnderstandingQuiz extends QuizScene{
  constructor() {
    super("TimeDecisionUnderstandingQuiz");
    this.points = 0;
  }

  preload() {
    super.preload();
    this.load.audio('intro', soundFactory.getSound("reading_game_tts_sentence","intro.mp3"));
    this.load.audio('day', soundFactory.getSound("reading_game_tts_sentence","day.mp3"));
    this.load.audio('pizza', soundFactory.getSound("reading_game_tts_sentence","pizza.mp3"));
    this.load.audio('football', soundFactory.getSound("reading_game_tts_sentence","football.mp3"));
    this.load.audio('spiders', soundFactory.getSound("reading_game_tts_sentence","spiders.mp3"));

  }

  create(){

    let soundtest = new QuizQuestion("day","", new Stimuli(null,"day"));
    soundtest.columnLayout = 2;
    soundtest.addAnswer(new Answer(stringFactory.getString("reading_game_tts_sentence_question1_a1"),true));
    soundtest.addAnswer(new Answer(stringFactory.getString("reading_game_tts_sentence_question1_a2"),false));
    this.addQuestion(soundtest);

    soundtest = new QuizQuestion("glory","", new Stimuli(null,"pizza"));
    soundtest.columnLayout = 2;
    soundtest.addAnswer(new Answer(stringFactory.getString("reading_game_tts_sentence_question2_a1"),true));
    soundtest.addAnswer(new Answer(stringFactory.getString("reading_game_tts_sentence_question2_a2"),false));
    soundtest.addAnswer(new Answer(stringFactory.getString("reading_game_tts_sentence_question2_a3"),false));
    soundtest.addAnswer(new Answer(stringFactory.getString("reading_game_tts_sentence_question2_a4"),false));
    this.addQuestion(soundtest);

    soundtest = new QuizQuestion("football","", new Stimuli(null,"football"));
    soundtest.columnLayout = 2;
    soundtest.addAnswer(new Answer(stringFactory.getString("reading_game_tts_sentence_question3_a1"),true));
    soundtest.addAnswer(new Answer(stringFactory.getString("reading_game_tts_sentence_question3_a2"),false));
    soundtest.addAnswer(new Answer(stringFactory.getString("reading_game_tts_sentence_question3_a3"),false));
    soundtest.addAnswer(new Answer(stringFactory.getString("reading_game_tts_sentence_question3_a4"),false));
    this.addQuestion(soundtest);


    soundtest = new QuizQuestion("spiders","", new Stimuli(null,"spiders"));
    soundtest.columnLayout = 2;
    soundtest.addAnswer(new Answer(stringFactory.getString("reading_game_tts_sentence_question4_a1"),true));
    soundtest.addAnswer(new Answer(stringFactory.getString("reading_game_tts_sentence_question4_a2"),false));
    soundtest.addAnswer(new Answer(stringFactory.getString("reading_game_tts_sentence_question4_a3"),false));
    soundtest.addAnswer(new Answer(stringFactory.getString("reading_game_tts_sentence_question4_a4"),false));
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

  //  document.getElementById("edit-submit").click();
    this.titleText.destroy();
    this.avatarButton.destroy();
    this.startButton.destroy();
    this.showNextQuestion();
  }


  questionFinishedHook(id,answer){
    console.log("Q finished",id,answer);
    if(answer.result){
      this.points++;
    }
    return super.questionFinishedHook(id,answer);

  }
  quizFinishedHook(){

    let result = this.points/this.quizQuestions.length;
    Director.changeScene("ReadingGameTTSSentence",{
      "id": "ReadingGameTTSSentence",
      "result": result,
    });
  }


}
