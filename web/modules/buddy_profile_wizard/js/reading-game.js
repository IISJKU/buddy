class ReadingGame extends QuizScene{
  constructor() {
    super("ReadingGame");
  }

  create(){


    let test = new QuizQuestion("banane","Die Bannane ist gelb?");
    test.addAnswer(new Answer("Ja",true));
    test.addAnswer(new Answer("Nein",false));
    this.addQuestion(test);

    test = new QuizQuestion("bier","Bier schmeckt gut?");
    test.addAnswer(new Answer("Ja",true));
    test.addAnswer(new Answer("Nein",false));
    this.addQuestion(test);


    this.createTitle(stringFactory.getString("math_game_title_1"));
    let readingGame = this;
    this.startButton = new IconButton(this,stringFactory.getString("math_game_start"),this.cameras.main.centerX, 300,"playIcon",function (){
      readingGame.startGame();
    });
    this.startButton.init();
    this.add.existing(this.startButton);



  }

  startGame(){
    this.titleText.destroy();
    this.startButton.destroy();
    this.showNextQuestion();
  }


  questionFinishedHook(id,answer){
    console.log("Q finished",id,answer);

    return super.questionFinishedHook(id,answer);

  }



}
