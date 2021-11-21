class TimeManagementQuiz extends QuizScene{
  constructor() {
    super("TimeManagementQuiz");
    this.points = 0;
  }

  preload() {
    super.preload();
    this.load.audio('intro', soundFactory.getSound("time_management","time_management_intro.mp3"));
    this.load.audio('q1', soundFactory.getSound("time_management","time_management_question1.mp3"));
    this.load.audio('q2', soundFactory.getSound("time_management","time_management_question2.mp3"));
    this.load.audio('q3', soundFactory.getSound("time_management","time_management_question3.mp3"));
    this.load.audio('q4', soundFactory.getSound("time_management","time_management_question4.mp3"));

    this.load.image('noIcon', 'modules/buddy_profile_wizard/assets/img/util/NoIcon.png');
    this.load.image('yesIcon', 'modules/buddy_profile_wizard/assets/img/util/YesIcon.png');

  }

  create(){

    let test = new QuizQuestion("q1","", new Stimuli(null,"q1"));
    test.addAnswer(new Answer(stringFactory.getString("yes"),true,"yesIcon"),false);
    test.addAnswer(new Answer(stringFactory.getString("no"),false,"noIcon"),false);
    this.addQuestion(test);

    test = new QuizQuestion("q2","", new Stimuli(null,"q2"));
    test.addAnswer(new Answer(stringFactory.getString("yes"),true,"yesIcon"),false);
    test.addAnswer(new Answer(stringFactory.getString("no"),false,"noIcon"),false);
    this.addQuestion(test);

    test = new QuizQuestion("q3","", new Stimuli(null,"q3"));
    test.addAnswer(new Answer(stringFactory.getString("yes"),true,"yesIcon"),false);
    test.addAnswer(new Answer(stringFactory.getString("no"),false,"noIcon"),false);
    this.addQuestion(test);

    test = new QuizQuestion("q4","", new Stimuli(null,"q4"));
    test.addAnswer(new Answer(stringFactory.getString("yes"),true,"yesIcon"),false);
    test.addAnswer(new Answer(stringFactory.getString("no"),false,"noIcon"),false);
    this.addQuestion(test);


    this.createTitle(stringFactory.getString("time_management_intro"));
    let timeManagmentQuiz = this;
    this.startButton = new IconButton(this,stringFactory.getString("math_game_start"),this.cameras.main.centerX, 300,"playIcon",function (){

      timeManagmentQuiz.startGame();
    });


    this.avatarButton = new AvatarAudioButton(this,"intro",this.cameras.main.centerX, 180,function (){

    });
    this.avatarButton.init();
    this.add.existing(this.avatarButton);
    this.startButton.init();
    this.add.existing(this.startButton);



  }

  startGame(){

    /*
    document.getElementById("edit-submit").click();
    */
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
