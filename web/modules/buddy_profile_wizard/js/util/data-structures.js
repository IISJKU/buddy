
class QuizQuestion{
  constructor(question,illustration) {
    this.question = question;
    this.illustration = illustration;
    this.columnLayout = 3;
    this.answers = [];
  }

  addAnswer(answer){
    this.answers.push(answer);
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
