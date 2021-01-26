# Product FAQ
The goal is to create extension that will let customers submit questions for each product. 
Admin can answer product question and list that question/answer to FAQ list on product page. 
Customer can see questions he submitted in My Account, along with replied answer.

Extension needs to have following features:
* Everyone can see list of frequent questions and answers on each product page
* Every question is related to one product
* Every question has only one answer
* Question needs to be related to website or store views - suggest and implement approach
* Logged in customer can submit new question from product page
* Logged in customer can see its submitted questions (and answers) in My Account ("My Questions").
    Every question also has link to associated product page
* For html on My Account Product pages try using default Magento classes and element structure, most of
elements are auto-styled that way. Use Magento javascript form validation if possible.
* Administrator can see all questions(grid, new on top) and can reply to them.
* Administrator can see which questions are unanswered in grid
* Administrator can edit/delete every question (and its owner)
* Administrator can decide if specific question and its answer is listed in FAQ section of that product on frontend

###Additional
* Administator gets email when Question is submitted. Implement as separate module.
* Implement up to two more features of your choice that you think would be usable on such extension.
* Administrator can relate Question (and its owner) to multiple products instead of one
