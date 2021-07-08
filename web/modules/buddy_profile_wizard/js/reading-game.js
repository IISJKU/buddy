class ReadingGame extends QuizScene{
  constructor() {
    super("ReadingGame");
  }

  create(){


    let test = new QuizQuestion("Hi how are you?");
    test.addAnswer(new Answer("yes","test1"));
    test.addAnswer(new Answer("no","test2"));
    test.addAnswer(new Answer("1no","tes11t2"));
    test.addAnswer(new Answer("123","tes11t2"));
    test.addAnswer(new Answer("22","test1"));
    this.addQuestion(test);

    test = new QuizQuestion("Hi how are you2?");
    test.addAnswer(new Answer("ye2s","test12"));
    test.addAnswer(new Answer("n2o","test22"));
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
    this.startQuiz();
  }


}
