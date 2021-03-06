class UnderstandingQuiz extends QuizScene{
  constructor() {
    super("UnderstandingQuiz");
    this.points = 0;
  }

  preload() {
    super.preload();
    this.load.audio('understanding_intro', soundFactory.getSound("understanding","understanding_intro.mp3"));
    this.load.audio('q1_understanding', soundFactory.getSound("understanding","understanding_question1.mp3"));
    this.load.audio('q2_understanding', soundFactory.getSound("understanding","understanding_question2.mp3"));
    this.load.audio('q3_understanding', soundFactory.getSound("understanding","understanding_question3.mp3"));


    this.load.image('noIcon', 'modules/buddy_profile_wizard/assets/img/util/NoIcon.png');
    this.load.image('yesIcon', 'modules/buddy_profile_wizard/assets/img/util/YesIcon.png');
  }

  create(){

    let test = new QuizQuestion("q1","", new Stimuli(null,"q1_understanding"));
    test.addAnswer(new Answer(stringFactory.getString("yes"),true,"yesIcon"),false);
    test.addAnswer(new Answer(stringFactory.getString("no"),false,"noIcon"),false);
    this.addQuestion(test);

    test = new QuizQuestion("q2","", new Stimuli(null,"q2_understanding"));
    test.addAnswer(new Answer(stringFactory.getString("yes"),true,"yesIcon"),false);
    test.addAnswer(new Answer(stringFactory.getString("no"),false,"noIcon"),false);
    this.addQuestion(test);

    test = new QuizQuestion("q3","", new Stimuli(null,"q3_understanding"));
    test.addAnswer(new Answer(stringFactory.getString("yes"),true,"yesIcon"),false);
    test.addAnswer(new Answer(stringFactory.getString("no"),false,"noIcon"),false);
    this.addQuestion(test);



    this.createTitle(stringFactory.getString("understanding_intro"));
    let understandingQuiz = this;
    this.startButton = new IconButton(this,stringFactory.getString("math_game_start"),this.cameras.main.centerX, 300,"playIcon",function (){

      understandingQuiz.startGame();
    });


    this.avatarButton = new AvatarAudioButton(this,"understanding_intro",this.cameras.main.centerX, 180,function (){

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
    Director.changeScene("UnderstandingQuiz",{
      "id": "Understanding",
      "result": result,
    });
  }


}
