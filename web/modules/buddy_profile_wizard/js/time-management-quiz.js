class TimeManagementQuiz extends QuizScene{
  constructor() {
    super("TimeManagementQuiz");
    this.points = 0;
  }

  preload() {
    super.preload();
    this.load.audio('time_management_intro', soundFactory.getSound("time_management","time_management_intro.mp3"));
    this.load.audio('q1_time_management', soundFactory.getSound("time_management","time_management_question1.mp3"));
    this.load.audio('q2_time_management', soundFactory.getSound("time_management","time_management_question2.mp3"));
    this.load.audio('q3_time_management', soundFactory.getSound("time_management","time_management_question3.mp3"));
    this.load.audio('q4_time_management', soundFactory.getSound("time_management","time_management_question4.mp3"));

    this.load.image('noIcon', 'modules/buddy_profile_wizard/assets/img/util/NoIcon.png');
    this.load.image('yesIcon', 'modules/buddy_profile_wizard/assets/img/util/YesIcon.png');

  }

  create(){

    let test = new QuizQuestion("q1_time_management","", new Stimuli(null,"q1_time_management"));
    test.addAnswer(new Answer(stringFactory.getString("yes"),true,"yesIcon"),false);
    test.addAnswer(new Answer(stringFactory.getString("no"),false,"noIcon"),false);
    this.addQuestion(test);

    test = new QuizQuestion("q2_time_management","", new Stimuli(null,"q2_time_management"));
    test.addAnswer(new Answer(stringFactory.getString("yes"),true,"yesIcon"),false);
    test.addAnswer(new Answer(stringFactory.getString("no"),false,"noIcon"),false);
    this.addQuestion(test);

    test = new QuizQuestion("q3_time_management","", new Stimuli(null,"q3_time_management"));
    test.addAnswer(new Answer(stringFactory.getString("yes"),true,"yesIcon"),false);
    test.addAnswer(new Answer(stringFactory.getString("no"),false,"noIcon"),false);
    this.addQuestion(test);

    test = new QuizQuestion("q4_time_management","", new Stimuli(null,"q4_time_management"));
    test.addAnswer(new Answer(stringFactory.getString("yes"),true,"yesIcon"),false);
    test.addAnswer(new Answer(stringFactory.getString("no"),false,"noIcon"),false);
    this.addQuestion(test);


    this.createTitle(stringFactory.getString("time_management_intro"));
    let timeManagmentQuiz = this;
    this.startButton = new IconButton(this,stringFactory.getString("math_game_start"),this.cameras.main.centerX, 300,"playIcon",function (){

      timeManagmentQuiz.startGame();
    });


    this.avatarButton = new AvatarAudioButton(this,"time_management_intro",this.cameras.main.centerX, 180,function (){

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
    Director.changeScene("TimeManagementQuiz",{
      "id": "TimeManagement",
      "result": result,
    });
  }


}
