class ReadingGameText extends QuizScene{
  constructor() {
    super("ReadingGameText");
  }

  preload() {
    super.preload();
    this.load.audio('intro_text', 'modules/buddy_profile_wizard/assets/sounds/reading_game_text/de/intro.mp3');


  }

  create(){

    let test = new QuizQuestion("banane","Die Bannane ist gelb?");
    test.addAnswer(new Answer("Nein",false));
    test.addAnswer(new Answer("Ja",true));
    this.addQuestion(test);

    test = new QuizQuestion("bier","Bier schmeckt gut?");
    test.addAnswer(new Answer("Nein",false));
    test.addAnswer(new Answer("Ja",true));
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

    return super.questionFinishedHook(id,answer);

  }

  quizFinishedHook() {

    console.log("FINISHED");
  }

  quizFinishedHook(){

    console.log("starting bla");
    Director.changeScene("ReadingGameText");
  }

}
