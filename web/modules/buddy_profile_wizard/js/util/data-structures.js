
class QuizQuestion{
  constructor(id,question,stimuli) {
    this.id = id;
    this.question = question;
    this.stimuli = stimuli;
    this.columnLayout = 3;
    this.answers = [];
  }

  addAnswer(answer,shuffle=true){
    this.answers.push(answer);
    if(shuffle){
      this.answers = gameUtil.shuffle(this.answers);
    }

  }

}


class Answer{
  constructor(text,result, icon,illustration) {
    this.text = text;
    this.icon = icon;
    this.result = result;
    this.illustration = illustration;
  }
}

class Stimuli{
  constructor(image,sound,video) {
    this.image = image;
    this.sound = sound;
    this.video = video;

  }
}
