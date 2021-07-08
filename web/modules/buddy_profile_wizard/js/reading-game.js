class ReadingGame extends QuizScene{
  constructor() {
    super("ReadingGame");
  }

  create(){


    let test = new QuizQuestion("MUh");
    test.addAnswer(new Answer("oarsch"))

    this.createTitle(stringFactory.getString("math_game_title_1"));
    let mathGame = this;
    this.startButton = new IconButton(this,stringFactory.getString("math_game_start"),this.cameras.main.centerX, 300,"playIcon",function (){
      mathGame.titleText.destroy();
      mathGame.startButton.destroy();
      mathGame.startGame();
    });
    this.startButton.init();
    this.add.existing(this.startButton);



  }
}
