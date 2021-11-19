class ReadingGameText extends QuizScene{
  constructor() {
    super("ReadingGameText");
    this.points = 0;
  }

  preload() {
    super.preload();
    this.load.audio('intro_text', soundFactory.getSound("reading_game_text","intro.mp3"));


  }

  create(){

    let test = new QuizQuestion("banane",stringFactory.getString("reading_game_text_question1"));
    test.addAnswer(new Answer(stringFactory.getString("yes"),true),false);
    test.addAnswer(new Answer(stringFactory.getString("no"),false),false);
    this.addQuestion(test);

    test = new QuizQuestion("bier",stringFactory.getString("reading_game_text_question2"));
    test.addAnswer(new Answer(stringFactory.getString("yes"),true));
    test.addAnswer(new Answer(stringFactory.getString("no"),false));
    this.addQuestion(test);


    this.createTitle(stringFactory.getString("reading_game_text_intro"));
    let readingGame = this;
    this.startButton = new IconButton(this,stringFactory.getString("math_game_start"),this.cameras.main.centerX, 300,"playIcon",function (){

      readingGame.startGame();
    });


    this.avatarButton = new AvatarAudioButton(this,"intro_text",this.cameras.main.centerX, 180,function (){

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
    if(answer.result){
      this.points++;
    }
    return super.questionFinishedHook(id,answer);

  }

  quizFinishedHook() {

    console.log("FINISHED");
  }

  quizFinishedHook(){

    let result = this.points/this.quizQuestions.length;
    Director.changeScene("ReadingGameText",{
      "id": "ReadingGameText",
      "result": result,
    });
  }

}
