
class QuizQuestion{
  constructor(question,illustration) {
    this.question = question;
    this.illustration = illustration;
    this.answers = [];
  }

  addAnswer(answer){
    this.question.push(answer);
  }

}


class Answer{
  constructor(text, icon,illustration) {
    this.text = text;
    this.icon = icon;
    this.illustration = illustration;
  }
}
